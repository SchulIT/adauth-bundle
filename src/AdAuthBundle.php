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
                ->arrayNode('hosts')
                    ->useAttributeAsKey('name')
                    ->beforeNormalization()->castToArray()->end()
                    ->requiresAtLeastOneElement()
                    ->arrayPrototype()
                        ->children()
                            ->stringNode('url')->isRequired()->end()
                            ->stringNode('peer_name')->end()
                            ->stringNode('peer_fingerprint')->end()
                            ->stringNode('ca_certificate_file')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    public function loadExtension(array $config, ContainerConfigurator $configurator, ContainerBuilder $container): void {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../config'));
        $loader->load('commands.yaml');

        $factory = new Definition(AdAuthFactory::class);
        $container->setDefinition('adauth.factory', $factory);

        $serviceReferences = [ ];

        foreach($config['hosts'] as $alias => $hostConfig) {
            $service = new Definition(AdAuth::class);
            $service->setFactory([$factory, 'createAdAuth']);
            $service->setArgument(0, $hostConfig['url']);
            $service->setArgument(1, $hostConfig);

            $id = sprintf('adauth.%s', $alias);
            $serviceReferences[] = new Reference($id);

            $container->setDefinition($id, $service);
        }

        $chain = new Definition(FailoverAdAuthChain::class);
        $chain->setFactory([$factory, 'createAdAuthChain']);
        $chain->setArgument(0, $serviceReferences);

        $container->setDefinition('adauth', $chain);
        $container->setAlias(AdAuthInterface::class, 'adauth');
    }
}
