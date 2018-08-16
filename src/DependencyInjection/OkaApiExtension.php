<?php
namespace Oka\ApiBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
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
		
		// Doctrine configuration
		$container->setAlias('oka_api.doctrine_registry', new Alias('doctrine', false));
		$definition = $container->getDefinition('oka_api.object_manager');
		$definition->replaceArgument(0, $config['model_manager_name']);
		$definition->setFactory([new Reference('oka_api.doctrine_registry'), 'getManager']);
		
		$this->createResponseConfig($config['response'], $container);
		
		// CORS support configuration
		if (!empty($config['cors'])) {
			$this->createCorsSupportConfig($config, $container);
		}
		
		// WSSE firewalls configuration
		if (true === $config['firewalls']['wsse']['enabled']) {
			$this->createWsseAuthenticationConfig($config, $container);
		}
		
		$this->createSecurityBehaviorsConfig($config, $container);
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
		$nonceHandlerId = $wsseConfig['nonce']['handler_id'];
		$nonceSavePath = $wsseConfig['nonce']['save_path'] ?: $container->getParameter('kernel.cache_dir') . '/oka_security/nonces';
		
		if (null === $nonceHandlerId) {
			$nonceHandlerId = 'oka_api.wsse.nonce.handler.file';
			$nonceHandlerDefintion = new Definition('Oka\ApiBundle\Security\Nonce\Storage\Handler\FileNonceHandler');
			$nonceHandlerDefintion->addArgument($nonceSavePath);
			$nonceHandlerDefintion->setPublic(false);
			$container->setDefinition($nonceHandlerId, $nonceHandlerDefintion);
		}
		
		// Configure wsse User provider
		$wsseUserProviderDefinition = new Definition('Oka\ApiBundle\Security\User\WsseUserProvider');
		$wsseUserProviderDefinition->addArgument(new Reference('oka_api.object_manager'));
		$wsseUserProviderDefinition->addArgument($userClass);
		$container->setDefinition('oka_api.wsse_user_provider', $wsseUserProviderDefinition);
		
		// Configure wsse User manipulator
		$wsseUserManipulatorDefinition = new Definition('Oka\ApiBundle\Util\WsseUserManipulator');
		$wsseUserManipulatorDefinition->addArgument(new Reference('oka_api.object_manager'));
		$wsseUserManipulatorDefinition->addArgument(new Reference('event_dispatcher'));
		$wsseUserManipulatorDefinition->addArgument($userClass);
		$container->setDefinition('oka_api.util.wsse_user_manipulator', $wsseUserManipulatorDefinition);
		
		// Configure wsse authentication provider
		$wsseAuthenticationProviderDefinition = $container->getDefinition('oka_api.wsse.security.authentication.provider');
		$wsseAuthenticationProviderDefinition->replaceArgument(1, new Reference($nonceHandlerId));
		
		// Configure wsse security firewall
		$wsseListenerDefinition = $container->getDefinition('oka_api.wsse.security.authentication.listener');
		$wsseListenerDefinition->addTag('monolog.logger', ['channel' => $wsseConfig['log_channel']]);
		
		// Configure wsse authorization voter
		if (true === $wsseConfig['enabled_allowed_ips_voter']) {
			$wsseUserAllowedIpsVoterDefinition = new Definition('Oka\ApiBundle\Security\Authorization\Voter\WsseUserAllowedIpsVoter');
			$wsseUserAllowedIpsVoterDefinition->addTag('security.voter');
			$wsseUserAllowedIpsVoterDefinition->setPublic(false);
			$container->setDefinition('oka_api.wsse.security.authorization.allowed_ips_voter', $wsseUserAllowedIpsVoterDefinition);
		}
	}
	
	private function createSecurityBehaviorsConfig(array $config, ContainerBuilder $container)
	{
		if (true === $config['security_behaviors']['password_updater']) {
			$definition = new Definition('Oka\ApiBundle\Doctrine\EventListener\UpdatePasswordSubscriber');
			$definition->addArgument(new Reference('oka_api.util.password_updater'));
			$definition->addTag('doctrine.event_subscriber');
			$definition->setPublic(false);
			$container->setDefinition('oka_api.update_password.doctrine_subscriber', $definition);
		}
	}
	
	private function createResponseConfig(array $config, ContainerBuilder $container)
	{
		$container->setParameter('oka_api.response.error_builder_class', $config['error_builder_class']);
		
		if (true === $config['compression']['enabled']) {
			if (null === $config['compression']['encoder']) {
				$config['compression']['encoder'] = 'oka_api.response.default_content_encoder';
				$container->setDefinition($config['compression']['encoder'], new Definition('Oka\ApiBundle\Encoder\ResponseContentEncoder'));
			}
			
			$definition = new Definition('Oka\ApiBundle\EventListener\ResponseCompressListener');
			$definition->addArgument(new Reference('oka_api.request_matcher.host'));
			$definition->addArgument(new Reference($config['compression']['encoder']));
			$definition->addTag('kernel.event_subscriber');
			$container->setDefinition('oka_api.response_compress.event_listener', $definition);
		}
	}
}
