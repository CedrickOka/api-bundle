<?php

namespace Oka\ApiBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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
		
		// Doctrine configuration
		$container->setParameter('oka_api.user_class', $config['user_class']);
		$container->setParameter('oka_api.client_class', $config['client_class']);
		$container->setParameter('oka_api.model_manager_name', $config['model_manager_name']);
		
		$container->setAlias('oka_api.doctrine_registry', new Alias('doctrine', false));
		$definition = $container->getDefinition('oka_api.object_manager');
		$definition->replaceArgument(0, $config['model_manager_name']);
		$definition->setFactory([new Reference('oka_api.doctrine_registry'), 'getManager']);
		
		// Default HTTP Host configuration
		$container->setParameter('oka_api.http_host', $config['host']);
		// Default log channel configuration
		$container->setParameter('oka_api.log_channel', $config['log_channel']);
		
		// CORS support configuration
		if (empty($config['cors'])) {
			$this->createCorsSupportConfig($config, $container);
		}
		
		// WSSE firewalls configuration
		if ($config['firewalls']['wsse']['enabled']) {
			$this->createWsseAuthenticationConfig($config, $container);
		}
		
		// JSON Web Token firewalls configuration
		if ($config['firewalls']['jwt']['enabled']) {
			$this->createJWTAuthenticationConfig($config, $container);
		}
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
		
// 		$container->setParameter('oka_api.wsse.log_channel', $wsseConfig['log_channel']);		
		$wsseListenerDefinition = $container->getDefinition('oka_api.wsse.security.authentication.listener');
		$wsseListenerDefinition->addTag('monolog.logger', ['channel' => $wsseConfig['log_channel']]);
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