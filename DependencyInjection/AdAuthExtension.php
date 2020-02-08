<?php

namespace AdAuthBundle\DependencyInjection;

use AdAuth\AdAuthInterface;
use AdAuth\Stream\TlsStream;
use AdAuth\Stream\UnencryptedStream;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AdAuthExtension extends Extension {
    /**
     * @inheritDoc
     */
    public function load(array $configs, ContainerBuilder $container) {
        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__) . '/Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('adauth.url', $config['url']);

        $def = $container->getDefinition(AdAuthInterface::class);
        $def->setArgument(0, $config['url']);
        $def->setArgument(1, $config['tls']);
        $def->setArgument(2, new Reference($config['serializer']));
    }

    public function getAlias() {
        return 'adauth';
    }
}