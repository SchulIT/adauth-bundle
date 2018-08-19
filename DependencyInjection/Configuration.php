<?php

namespace AdAuthBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface {

    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder() {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('adauth');

        $rootNode
            ->children()
                ->scalarNode('url')
                    ->isRequired()
                ->end()
                ->arrayNode('tls')
                    ->children()
                        ->scalarNode('serialnumber')->end()
                        ->scalarNode('ca_certificate_file')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}