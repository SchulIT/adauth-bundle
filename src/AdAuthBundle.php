<?php

namespace AdAuthBundle;

use AdAuth\AdAuth;
use AdAuth\AdAuthInterface;
use AdAuth\FailoverAdAuthChain;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
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
                ->arrayNode('failover')
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->scalarNode('url')->isRequired()->end()
                        ->arrayNode('tls')
                            ->children()
                                ->scalarNode('peer_name')->end()
                                ->scalarNode('peer_fingerprint')->end()
                                ->scalarNode('ca_certificate_file')->end()
                            ->end()
                        ->end()
                    ->end()
            ->end();
    }

    public function loadExtension(array $config, ContainerConfigurator $configurator, ContainerBuilder $container): void {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../config'));
        $loader->load('commands.yaml');

        $container->setParameter('adauth.url', $config['url']);

        $factory = new Definition(AdAuthFactory::class);
        $container->setDefinition('adauth.factory', $factory);

        $primary = new Definition(AdAuth::class);
        $primary->setFactory([$factory, 'createAdAuth']);
        $primary->setArgument(0, $config['url']);
        $primary->setArgument(1, $config['tls']);
        $primary->setPublic(true);

        $container->setDefinition('adauth.primary', $primary);

        if(isset($config['failover']['enabled']) && $config['failover']['enabled']) { // FAILOVER
            $secondary = new Definition(AdAuth::class);
            $secondary->setFactory([$factory, 'createAdAuth']);
            $secondary->setArgument(0, $config['failover']['url']);
            $secondary->setArgument(1, $config['failover']['tls']);

            $container->setDefinition('adauth.secondary', $secondary);

            $chain = new Definition(AdAuthInterface::class);
            $chain->setClass(FailoverAdAuthChain::class);
            $chain->setArgument(0, [
                new Reference('adauth.primary'),
                new Reference('adauth.secondary'),
            ]);

            $container->setDefinition('adauth', $chain);
            $container->setAlias(AdAuthInterface::class, 'adauth');
        } else { // NO FAILOVER
            $container->setAlias(AdAuthInterface::class, 'adauth.primary');
            $container->setAlias('adauth', 'adauth.primary');
        }
    }
}
