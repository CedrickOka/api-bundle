<?php
namespace Oka\ApiBundle\DependencyInjection;

use Oka\ApiBundle\Security\Authorization\Voter\WsseUserAllowedIpsVoter;
use Oka\ApiBundle\Security\User\WsseUserProvider;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Oka\ApiBundle\Util\WsseUserManipulator;
use Oka\ApiBundle\Http\WsseRequestMatcher;
use Oka\ApiBundle\Security\Authentication\Provider\WsseProvider;

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
			}
		}
		
		$container->setParameter('oka_api.wsse_user_class', $userClass);
		
		// Configure wsse user provider
		$wsseUserProviderDefinition = new Definition(WsseUserProvider::class);
		$wsseUserProviderDefinition->addArgument(new Reference('oka_api.object_manager'));
		$wsseUserProviderDefinition->addArgument($userClass);
		$container->setDefinition('oka_api.wsse_user_provider', $wsseUserProviderDefinition);
		
		// Configure wsse user manipulator
		$wsseUserManipulatorDefinition = new Definition(WsseUserManipulator::class);
		$wsseUserManipulatorDefinition->addArgument(new Reference('oka_api.object_manager'));
		$wsseUserManipulatorDefinition->addArgument(new Reference('event_dispatcher'));
		$wsseUserManipulatorDefinition->addArgument($userClass);
		$container->setDefinition('oka_api.util.wsse_user_manipulator', $wsseUserManipulatorDefinition);
		
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
// User Provider
//    oka_api.jwt_user_provider:
//        class: Oka\ApiBundle\Security\User\JwtUserProvider
//        arguments: [ '@oka_api.object_manager', '%oka_api.user_class%' ]
		
		$jwtConfig = $config['firewalls']['jwt'];
		$container->setParameter('oka_api.jwt.authentication.token_ttl', $jwtConfig['token']['ttl']);
		$container->setParameter('oka_api.jwt.log_channel', $jwtConfig['log_channel']);
		
		$requestMatcherDefinition = $container->getDefinition('oka_api.jwt.firewall.request_matcher');
		$requestMatcherDefinition->replaceArgument(1, $jwtConfig['token']['extractors']);
		
		$this->createJWTTokenEncoder($jwtConfig['token']['encoder'], $container);
		$this->createJWTTokenExtractors($jwtConfig['token']['extractors'], $container);
	}
	
	private function createJWTTokenEncoder(array $config, ContainerBuilder $container)
	{
		
	}
	
	private function createJWTTokenExtractors(array $config, ContainerBuilder $container)
	{
		if ($config['authorization_header']['enabled']) {
// 			$headerExtractorDefinition = $container->getDefinition('oka_api.jwt.authentication.token_extractor.authorization_header');
// 			$headerExtractorDefinition->replaceArgument(0, $config['authorization_header']['prefix']);
// 			$headerExtractorDefinition->replaceArgument(1, $config['authorization_header']['name']);
		}
		
		if ($config['query_parameter']['enabled']) {
			
		}
		
		if ($config['cookie']['enabled']) {
			
		}
	}
}