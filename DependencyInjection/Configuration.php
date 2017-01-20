<?php

namespace Oka\ApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root('oka_api');
		
		$rootNode
				->addDefaultsIfNotSet()
				->children()
					->scalarNode('user_class')
						->isRequired()
						->cannotBeEmpty()
					->end()
					->scalarNode('wsse_user_class')
						->isRequired()
						->cannotBeEmpty()
					->end()
					->scalarNode('model_manager_name')
						->defaultNull()
					->end()
					->scalarNode('host')
						->cannotBeEmpty()
						->info('This value represents API base http host.')
					->end()
					->append($this->addLogChannelNode())
					->arrayNode('cors')
						->canBeUnset()
						->cannotBeEmpty()
						->children()
							->arrayNode('expose_headers')
								->prototype('scalar')->end()
							->end()
							->arrayNode('allowed_origins')
								->prototype('scalar')->end()
							->end()
						->end()
						->info('This value permit to enable CORS protocole support.')
					->end()
					->arrayNode('firewalls')
						->addDefaultsIfNotSet()
						->children()
							->arrayNode('wsse')
// 								->canBeDisabled() TODO has use
								->addDefaultsIfNotSet()
								->children()
									->append($this->addLogChannelNode())
								->end()
							->end()
							->arrayNode('jwt')
// 								->canBeDisabled() TODO has use
								->addDefaultsIfNotSet()
								->children()
									->append($this->addLogChannelNode())
									->arrayNode('auth_id')
										->addDefaultsIfNotSet()
										->children()
											->scalarNode('route_key')->defaultValue('authId')->end()
											->scalarNode('method_name')->defaultValue('getId')->end()
										->end()
									->end()
								->end()
							->end()
						->end()
						->info('This value configure API firewalls.')
					->end()
				->end();

		// Exemple Configuration
// 		oka_api:
// 			user_class: Aynid\UserBundle\Entity\User
// 			wsse_user_class: Aynid\Api\CoreBundle\Entity\Client
// 			host: "%web_host.api%"
// 			log_channel: "api"
// 			cors:
// 				allowed_origins: [ "http://%web_host%" ]
// 			firewalls:
// 				wsse:
// 					log_channel: "wsse"
// 				jwt:
// 					log_channel: "jwt"
// 					auth_id:
// 						route_key: "userId"
// 						method_name: "getId"

		return $treeBuilder;
	}
	
	public function addLogChannelNode()
	{
		$node = new ScalarNodeDefinition('log_channel');
		$node
			->defaultValue('api')
		->end();
		
		return $node;
	}
}