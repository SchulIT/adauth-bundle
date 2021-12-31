<?php

namespace AdAuthBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface {

    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder(): TreeBuilder {
        $treeBuilder = new TreeBuilder('adauth');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('serializer')
                    ->defaultValue('jms_serializer')
                ->end()
                ->scalarNode('url')
                    ->isRequired()
                ->end()
                ->arrayNode('tls')
                    ->children()
                        ->scalarNode('peer_name')->end()
                        ->scalarNode('peer_fingerprint')->end()
                        ->scalarNode('ca_certificate_file')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}