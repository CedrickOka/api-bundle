<?php

namespace Oka\ApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Oka\ApiBundle\CorsOptions;

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
					->append($this->getLogChannelNodeDefinition())
					->append($this->getCorsNodeDefinition())
					->arrayNode('firewalls')
						->addDefaultsIfNotSet()
						->children()
							->arrayNode('wsse')
// 								->canBeDisabled() TODO has use
								->addDefaultsIfNotSet()
								->children()
									->append($this->getLogChannelNodeDefinition())
								->end()
							->end()
							->append($this->getJWTFirewallNodeDefintion())
// 							->arrayNode('jwt')
// 								->canBeDisabled()
// 								->addDefaultsIfNotSet()
// 								->children()
// 									->append($this->getLogChannelNodeDefinition())
// 									->arrayNode('auth_id')
// 										->addDefaultsIfNotSet()
// 										->children()
// 											->scalarNode('route_key')->defaultValue('authId')->end()
// 											->scalarNode('method_name')->defaultValue('getId')->end()
// 										->end()
// 									->end()
// 								->end()
// 							->end()
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
// 				allow_origin: [ "http://%web_host%" ]
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
	
	public function getCorsNodeDefinition()
	{
		$node = new ArrayNodeDefinition('cors');
		$node
			->canBeUnset()
			->cannotBeEmpty()
			->requiresAtLeastOneElement()
			->useAttributeAsKey('name')
			->info('This value permit to enable CORS protocole support.')
			->prototype('array')
				->children()
					->scalarNode(CorsOptions::HOST)->defaultNull()->end()
					->scalarNode(CorsOptions::PATTERN)->defaultNull()->end()
					->arrayNode(CorsOptions::ALLOW_ORIGIN)
						->performNoDeepMerging()
						->prototype('scalar')->end()
					->end()
					->arrayNode(CorsOptions::ALLOW_METHODS)
						->performNoDeepMerging()
						->prototype('scalar')->end()
					->end()
					->arrayNode(CorsOptions::ALLOW_HEADERS)
						->performNoDeepMerging()
						->prototype('scalar')->end()
					->end()
					->booleanNode(CorsOptions::ALLOW_CREDENTIALS)->defaultFalse()->end()
					->arrayNode(CorsOptions::EXPOSE_HEADERS)
						->performNoDeepMerging()
						->prototype('scalar')->end()
					->end()
					->integerNode(CorsOptions::MAX_AGE)->defaultValue(3600)->end()
				->end()
			->end()
		->end();
		
		return $node;
	}
	
	public function getLogChannelNodeDefinition()
	{
		$node = new ScalarNodeDefinition('log_channel');
		$node->defaultValue('api')->end();
		
		return $node;
	}
	
	public function getJWTFirewallNodeDefintion()
	{
		$node = new ArrayNodeDefinition('jwt');
		$node
			->canBeDisabled()
			->addDefaultsIfNotSet()
			->children()
				->append($this->getLogChannelNodeDefinition())
				->arrayNode('token')
					->addDefaultsIfNotSet()
					->cannotBeEmpty()
					->children()
						->arrayNode('user_field_map')
							->addDefaultsIfNotSet()
							->children()
								->arrayNode('private_claims')
									->addDefaultsIfNotSet()
									->children()
										->scalarNode('jti')->isRequired()->treatNullLike('username')->end()
									->end()
								->end()
								->arrayNode('public_claims')
									->useAttributeAsKey('name')
									->prototype('scalar')->end()
								->end()
							->end()
						->end()
					->end()
				->end()
			->end()
		->end();
		
		return $node;
	}
}