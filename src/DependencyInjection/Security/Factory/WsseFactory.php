<?php
namespace Oka\ApiBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class WsseFactory implements SecurityFactoryInterface
{
	public function getKey()
	{
		return 'wsse';
	}
	
	public function getPosition()
	{
		return 'pre_auth';
	}
	
	public function create(ContainerBuilder $container, $id, $config, $clientProvider, $defaultEntryPoint)
	{
		$providerId = 'security.authentication.provider.wsse.'.$id;
		$container->setDefinition($providerId, new ChildDefinition('oka_api.wsse.security.authentication.provider'))
				  ->replaceArgument(0, new Reference($clientProvider))
				  ->replaceArgument(2, $config['lifetime']);
		
		$listenerId = 'security.authentication.listener.wsse.'.$id;
		$container->setDefinition($listenerId, new ChildDefinition('oka_api.wsse.security.authentication.listener'));
		
		return [$providerId, $listenerId, $defaultEntryPoint];
	}
	
	public function addConfiguration(NodeDefinition $builder)
	{
		$builder
			->children()
				->scalarNode('lifetime')->defaultValue(300)->end()
			->end();
	}
}
