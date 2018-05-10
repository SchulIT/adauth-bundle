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
                ->scalarNode('host')
                    ->isRequired()
                ->end()
                ->integerNode('port')
                    ->defaultValue(55117)
                ->end()
                ->enumNode('transport')
                    ->values(['unencrypted', 'tls'])
                    ->defaultValue('unencrypted')
                ->end()
                ->arrayNode('tls')
                    ->children()
                        ->scalarNode('peer_name')->end()
                        ->scalarNode('serialnumber')->end()
                        ->scalarNode('ca_certificate_file')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}