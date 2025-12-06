<?php

namespace AdAuthBundle;

use AdAuth\AdAuthInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class AdAuthBundle extends AbstractBundle {
    public function configure(DefinitionConfigurator $definition): void {
        $definition->rootNode()
            ->children()
                ->scalarNode('url')->isRequired()->end()
                ->arrayNode('tls')
                    ->children()
                        ->scalarNode('peer_name')->end()
                        ->scalarNode('peer_fingerprint')->end()
                        ->scalarNode('ca_certificate_file')->end()
                    ->end()
                ->end()
            ->end();
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void {
        $loader = new YamlFileLoader($builder, new FileLocator(__DIR__ . '/../config'));
        $loader->load('services.yaml');
        $loader->load('commands.yaml');

        $builder->setParameter('adauth.url', $config['url']);

        $def = $builder->getDefinition(AdAuthInterface::class);
        $def->setArgument(0, $config['url']);
        $def->setArgument(1, $config['tls']);
    }
}
