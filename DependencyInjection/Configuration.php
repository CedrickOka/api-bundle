<?php
namespace Oka\ApiBundle\DependencyInjection;

use Oka\ApiBundle\CorsOptions;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
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
// 					->scalarNode('user_class')
// 						->isRequired()
// 						->cannotBeEmpty()
// 					->end()
					->scalarNode('client_class')
						->defaultNull()
						->info('This configuration value is deprecated use instead `oka_api.firewalls.wsse.user_class`')
					->end()
					->scalarNode('model_manager_name')
						->defaultNull()
					->end()
					->scalarNode('host')
						->defaultNull()
						->info('This value represents API base http host.')
					->end()
					->append($this->getLogChannelNodeDefinition())
					->append($this->getCorsNodeDefinition())
					->arrayNode('firewalls')
						->addDefaultsIfNotSet()
						->children()
							->arrayNode('wsse')
								->canBeDisabled()
								->addDefaultsIfNotSet()
								->children()
									->scalarNode('user_class')->defaultNull()->end()
									->arrayNode('nonce')
										->addDefaultsIfNotSet()
										->children()
											->scalarNode('save_path')->defaultNull()->end()
											->scalarNode('handler_id')->defaultNull()->end()
										->end()
									->end()
									->booleanNode('enabled_allowed_ips_voter')
										->defaultTrue()
										->info('Allow authorization with ips.')
									->end()
									->append($this->getLogChannelNodeDefinition('wsse'))
								->end()
							->end()
// 							->append($this->getJWTFirewallNodeDefintion('jwt'))
						->end()
						->info('This value configure API firewalls.')
					->end()
					->arrayNode('response')
						->addDefaultsIfNotSet()
						->children()
							->scalarNode('error_builder_class')->defaultNull()->end()
						->end()
					->end()
				->end();
		
		return $treeBuilder;
	}
	
	public function getLogChannelNodeDefinition($defaultValue = 'api')
	{
		$node = new ScalarNodeDefinition('log_channel');
		$node->defaultValue($defaultValue)->end();
		
		return $node;
	}
	
	public function getCorsNodeDefinition()
	{
		$node = new ArrayNodeDefinition('cors');
		$node
			->canBeUnset()
			->requiresAtLeastOneElement()
			->useAttributeAsKey('name')
			->info('This value permit to enable CORS protocol support.')
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
	
	public function getJWTFirewallNodeDefintion()
	{
		$authorizationHeaderPrefixNode = new ScalarNodeDefinition('prefix');
		$authorizationHeaderPrefixNode->defaultValue('Bearer')->end();
		
		$node = new ArrayNodeDefinition('jwt');
		$node
			->addDefaultsIfNotSet()
			->canBeDisabled()
			->children()
				->append($this->getLogChannelNodeDefinition())
				->arrayNode('token')
					->addDefaultsIfNotSet()
					->cannotBeEmpty()
					->children()
						->integerNode('ttl')->defaultValue(3600)->end()
						->arrayNode('encoder')
							->addDefaultsIfNotSet()
							->children()
								->scalarNode('service')->defaultValue('oka_api.jwt.authentication.default_encoder')->end()
								->scalarNode('crypto_engine')->defaultValue('openssl')->end()
								->scalarNode('signature_algorithm')->defaultValue('Sha256')->end()
							->end()
						->end()
						->arrayNode('extractors')
							->addDefaultsIfNotSet()
							->children()
								->append($this->getJWTExtractorNodeDefinition('authorization_header', 'Authorization', [$authorizationHeaderPrefixNode]))
								->append($this->getJWTExtractorNodeDefinition('query_parameter', 'bearer'))
								->append($this->getJWTExtractorNodeDefinition('cookie', 'BEARER'))
							->end()
						->end()
						->scalarNode('user_identity_field')->defaultValue('username')->end()
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
	
	public function getJWTExtractorNodeDefinition($name, $defaultValue = null, array $appendNodes = []) {
		$node = new ArrayNodeDefinition($name);
		
		$childrenNode = $node
			->addDefaultsIfNotSet()
			->canBeDisabled()
			->children()
				->scalarNode('name')->defaultValue($defaultValue)->end();
		
		if (!empty($appendNodes)) {
			/** @var NodeDefinition $appendNode */
			foreach ($appendNodes as $appendNode) {
				$childrenNode->append($appendNode);
			}
		}
		
			$childrenNode->end()
		->end();
		
		return $node;
	}
}