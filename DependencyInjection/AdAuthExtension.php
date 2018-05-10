<?php

namespace AdAuthBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AdAuthExtension extends Extension {
    /**
     * @inheritDoc
     */
    public function load(array $configs, ContainerBuilder $container) {
        $loader = new XmlFileLoader($container, new FileLocator(dirname(__DIR__) . '/Resources/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('adauth.host', $config['host']);
        $container->setParameter('adauth.port', $config['port']);
        $container->setParameter('adauth.transport', $config['transport']);

        if($container->getParameter('adauth.transport') === 'tls') {
            $container->setParameter('adauth.transport.tls.peer_name', $config['tls']['peer_name']);
            $container->setParameter('adauth.transport.tls.serialnumber', $config['tls']['serialnumber']);
            $container->setParameter('adauth.transport.tls.ca_certificate_file', $config['tls']['ca_certificate_file']);

            $def = $container->getDefinition('adauth.transport.tls');
            $def->replaceArgument(0, $config['tls']['peer_name']);
            $def->replaceArgument(1, $config['tls']['serialnumber']);
            $def->replaceArgument(2, $config['tls']['ca_certificate_file']);
        }

        $def = $container->getDefinition('adauth');
        $def->replaceArgument(0, $container->getParameter('adauth.host'));
        $def->replaceArgument(1, $container->getParameter('adauth.port'));

        if($container->getParameter('adauth.transport') === 'tls') {
            $def->replaceArgument(2, new Reference('adauth.transport.tls'));
        } else {
            $def->replaceArgument(2, new Reference('adauth.transport.unencrypted'));
        }

        $def->replaceArgument(3, new Reference('jms_serializer'));
    }

    public function getAlias() {
        return 'adauth';
    }
}