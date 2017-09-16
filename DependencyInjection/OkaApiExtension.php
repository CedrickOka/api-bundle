<?php
namespace Oka\ApiBundle\DependencyInjection;

use Oka\ApiBundle\Security\Authorization\Voter\WsseUserAllowedIpsVoter;
use Oka\ApiBundle\Security\Nonce\Storage\Handler\FileNonceHandler;
use Oka\ApiBundle\Security\Nonce\Storage\NativeNonceStorage;
use Oka\ApiBundle\Security\User\WsseUserProvider;
use Oka\ApiBundle\Util\WsseUserManipulator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class OkaApiExtension extends Extension
{
	/**
	 * {@inheritdoc}
	 */
	public function load(array $configs, ContainerBuilder $container)
	{
		$configuration = new Configuration();
		$config = $this->processConfiguration($configuration, $configs);

		$loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
		$loader->load('services.yml');
		
		// Parameters Configuration
		$container->setParameter('oka_api.http_host', $config['host']);
		$container->setParameter('oka_api.log_channel', $config['log_channel']);
		$container->setParameter('oka_api.client_class', $config['client_class']);
		$container->setParameter('oka_api.response.error_builder_class', $config['response']['error_builder_class']);
		
		// Doctrine configuration
		$container->setAlias('oka_api.doctrine_registry', new Alias('doctrine', false));
		$definition = $container->getDefinition('oka_api.object_manager');
		$definition->replaceArgument(0, $config['model_manager_name']);
		$definition->setFactory([new Reference('oka_api.doctrine_registry'), 'getManager']);
		
		// CORS support configuration
		if (!empty($config['cors'])) {
			$this->createCorsSupportConfig($config, $container);
		}
		
		// WSSE firewalls configuration
		if (true === $config['firewalls']['wsse']['enabled']) {
			$this->createWsseAuthenticationConfig($config, $container);
		}
		
		// JSON Web Token firewalls configuration
// 		if ($config['firewalls']['jwt']['enabled']) {
// 			$this->createJWTAuthenticationConfig($config, $container);
// 		}
	}
	
	private function createCorsSupportConfig(array $config, ContainerBuilder $container)
	{
		// Enable CORS Listener
		$definition = $container->getDefinition('oka_api.cors_support.event_listener');
		$definition->replaceArgument(0, $config['cors'])
				   ->addTag('kernel.event_subscriber')
				   ->setPublic(true);
	}
	
	private function createWsseAuthenticationConfig(array $config, ContainerBuilder $container)
	{
		$wsseConfig = $config['firewalls']['wsse'];
		
		if (!$userClass = $wsseConfig['user_class']) {
			if (!$userClass = $config['client_class']) {
				throw new InvalidConfigurationException('The child node "user_class" at path "oka_api.firewalls.wsse" must be configured if wsse firewall is enabled.');
			} else {
				@trigger_error('The configuration value `oka_api.client_class` is deprecated since version 1.4.0. Use `oka_api.firewalls.wsse.user_class` instead', E_USER_DEPRECATED);				
			}
		}
		$container->setParameter('oka_api.wsse_user_class', $userClass);
		
		// Configure Nonce
		$nonceSavePath = $wsseConfig['nonce']['save_path'] ?: $container->getParameter('kernel.cache_dir') . '/oka_security/nonces';
		$nonceHandlerId = $wsseConfig['nonce']['handler_id'];
		
		if (null === $nonceHandlerId) {
			$nonceHandlerId = 'oka_api.wsse.nonce.handler.file';
			$nonceHandlerDefintion = new Definition(FileNonceHandler::class);
			$nonceHandlerDefintion->addArgument($nonceSavePath);
			$nonceHandlerDefintion->setPublic(false);
			$container->setDefinition($nonceHandlerId, $nonceHandlerDefintion);
		}
		
		$nonceStorageDefinition = new Definition(NativeNonceStorage::class);
		$nonceStorageDefinition->addArgument(new Reference($nonceHandlerId));
		$nonceStorageDefinition->addArgument($nonceSavePath);
		$nonceStorageDefinition->setPublic(false);
		$container->setDefinition('oka_api.wsse.nonce.native_storage', $nonceStorageDefinition);
		
		// Configure wsse User provider
		$wsseUserProviderDefinition = new Definition(WsseUserProvider::class);
		$wsseUserProviderDefinition->addArgument(new Reference('oka_api.object_manager'));
		$wsseUserProviderDefinition->addArgument($userClass);
		$container->setDefinition('oka_api.wsse_user_provider', $wsseUserProviderDefinition);
		
		// Configure wsse User manipulator
		$wsseUserManipulatorDefinition = new Definition(WsseUserManipulator::class);
		$wsseUserManipulatorDefinition->addArgument(new Reference('oka_api.object_manager'));
		$wsseUserManipulatorDefinition->addArgument(new Reference('event_dispatcher'));
		$wsseUserManipulatorDefinition->addArgument($userClass);
		$container->setDefinition('oka_api.util.wsse_user_manipulator', $wsseUserManipulatorDefinition);
		
		// Configure wsse authentication provider
		$wsseAuthenticationProviderDefinition = $container->getDefinition('oka_api.wsse.security.authentication.provider');
		$wsseAuthenticationProviderDefinition->replaceArgument(1, new Reference('oka_api.wsse.nonce.native_storage'));
		
		// Configure wsse security firewall
		$wsseListenerDefinition = $container->getDefinition('oka_api.wsse.security.authentication.listener');
		$wsseListenerDefinition->addTag('monolog.logger', ['channel' => $wsseConfig['log_channel']]);
		
		// Configure wsse authorization voter
		if (true === $wsseConfig['enabled_allowed_ips_voter']) {
			$wsseUserAllowedIpsVoterDefinition = new Definition(WsseUserAllowedIpsVoter::class);
			$wsseUserAllowedIpsVoterDefinition->addTag('security.voter');
			$wsseUserAllowedIpsVoterDefinition->setPublic(false);
			$container->setDefinition('oka_api.wsse.security.authorization.allowed_ips_voter', $wsseUserAllowedIpsVoterDefinition);
		}
	}
	
	private function createJWTAuthenticationConfig(array $config, ContainerBuilder $container)
	{
		$jwtConfig = $config['firewalls']['jwt'];
		$container->setParameter('oka_api.jwt.authentication.token_ttl', $jwtConfig['token']['ttl']);
		$container->setParameter('oka_api.jwt.log_channel', $jwtConfig['log_channel']);
		
		$requestMatcherDefinition = $container->getDefinition('oka_api.jwt.firewall.request_matcher');
		$requestMatcherDefinition->replaceArgument(1, $jwtConfig['token']['extractors']);
		
		$this->createJWTTokenEncoder($jwtConfig['token']['encoder'], $container);
		$this->createJWTTokenExtractors($jwtConfig['token']['extractors'], $container);
	}
	
	private function createJWTTokenEncoder(array $config, ContainerBuilder $container) {}
	
	private function createJWTTokenExtractors(array $config, ContainerBuilder $container)
	{
		if ($config['authorization_header']['enabled']) {
// 			$headerExtractorDefinition = $container->getDefinition('oka_api.jwt.authentication.token_extractor.authorization_header');
// 			$headerExtractorDefinition->replaceArgument(0, $config['authorization_header']['prefix']);
// 			$headerExtractorDefinition->replaceArgument(1, $config['authorization_header']['name']);
		}
		
		if ($config['query_parameter']['enabled']) {}
		
		if ($config['cookie']['enabled']) {}
	}
}