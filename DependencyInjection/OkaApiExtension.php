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
		$container->setParameter('oka_api.wsse_user_class', $config['wsse_user_class']);
		$container->setParameter('oka_api.model_manager_name', $config['model_manager_name']);
		
		$container->setAlias('oka_api.doctrine_registry', new Alias('doctrine', false));
		$definition = $container->getDefinition('oka_api.object_manager');
		$definition->setFactory([new Reference('oka_api.doctrine_registry'), 'getManager']);
		
		// Default HTTP Host configuration
		$container->setParameter('oka_api.http_host', $config['host']);
		
		// Default log channel configuration
		$container->setParameter('oka_api.log_channel', $config['log_channel']);
		
		// Firewalls configuration
		// TODO Firewalls allow activation on demand (canBeEnbaled())
		
		// WSSE firewalls configuration
		$container->setParameter('oka_api.wsse.log_channel', $config['firewalls']['wsse']['log_channel']);
		
		// JSON Web Token firewalls configuration
		$container->setParameter('oka_api.jwt.log_channel', $config['firewalls']['jwt']['log_channel']);
// 		$container->setParameter('oka_api.jwt.auth_id.route_key', $config['firewalls']['jwt']['auth_id']['route_key']);
// 		$container->setParameter('oka_api.jwt.auth_id.method_name', $config['firewalls']['jwt']['auth_id']['method_name']);
		$container->setParameter('oka_api.jwt.auth_id.route_key', 'userId');
		$container->setParameter('oka_api.jwt.auth_id.method_name', 'getId');
		
		// CORS support configuration
		if (isset($config['cors'])) {
			$container->setParameter('oka_api.cors.parameters', $config['cors'] ?: []);
			
			// Enable CORS Listener
			$definition = $container->getDefinition('oka_api.cors_support.event_listener');
			$definition->replaceArgument(0, $config['cors'])
					   ->addTag('kernel.event_subscriber')
					   ->setPublic(true);
		}
	}
}