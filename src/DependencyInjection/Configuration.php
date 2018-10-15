<?php
namespace Oka\ApiBundle\DependencyInjection;

use Oka\ApiBundle\CorsOptions;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

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
								->addDefaultsIfNotSet()
								->canBeEnabled()
								->children()
									->scalarNode('user_class')->defaultNull()->end()
									->arrayNode('nonce')
										->addDefaultsIfNotSet()
										->children()
											->scalarNode('storage_id')
												->info('This configuration value is deprecated and must be removed in version `1.9.0`')
												->defaultNull()
											->end()
											->scalarNode('handler_id')->defaultNull()->end()
											->scalarNode('save_path')->defaultNull()->end()
										->end()
									->end()
									->booleanNode('enabled_allowed_ips_voter')
										->defaultTrue()
										->info('Allow authorization with ips.')
									->end()
									->append($this->getLogChannelNodeDefinition('wsse'))
								->end()
							->end()
						->end()
						->info('This value configure API firewalls.')
					->end()
					->arrayNode('security_behaviors')
						->addDefaultsIfNotSet()
						->children()
							->booleanNode('password_updater')->defaultFalse()->end()
							->scalarNode('handler_id')->defaultNull()->end()
						->end()
					->end()
					->arrayNode('response')
						->addDefaultsIfNotSet()
						->children()
							->scalarNode('error_builder_class')->defaultNull()->end()
							->arrayNode('compression')
								->addDefaultsIfNotSet()
								->canBeEnabled()
								->children()
									->scalarNode('encoder')
										->defaultNull()
										->info('The service ID of encoder. The class must be implement `ResponseContentEncoderInterface` interface.')
									->end()
								->end()
							->children()
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
					->scalarNode(CorsOptions::PATTERN)->defaultNull()->end()
					->arrayNode(CorsOptions::ORIGINS)
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
	
	public function getJWTExtractorNodeDefinition($name, $defaultValue = null, array $appendNodes = [])
	{
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
