<?php
namespace Oka\ApiBundle;

use Oka\ApiBundle\DependencyInjection\Security\Factory\WsseFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OkaApiBundle extends Bundle
{
	public function build(ContainerBuilder $container)
	{
		parent::build($container);
		
		$extension = $container->getExtension('security');
		$extension->addSecurityListenerFactory(new WsseFactory());		
	}
}
