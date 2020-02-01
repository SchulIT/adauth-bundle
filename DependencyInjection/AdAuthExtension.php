<?php

namespace AdAuthBundle\DependencyInjection;

use AdAuth\AdAuthInterface;
use AdAuth\Stream\TlsStream;
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

        $options = $this->resolveOptions($config['url']);

        $container->setParameter('adauth.url', $config['url']);

        if(isset($config['tls']['serialnumber'])) {
            $container->setParameter('adauth.transport.tls.serialnumber', $config['tls']['serialnumber']);
        }

        $container->setParameter('adauth.host', $options['host']);
        $container->setParameter('adauth.port', $options['port']);
        $container->setParameter('adauth.transport', $options['transport']);

        if($options['transport'] === 'tls') {
            $container->setParameter('adauth.transport.tls.peer_name', $config['tls']['peer_name']);
            $container->setParameter('adauth.transport.tls.ca_certificate_file', $config['tls']['ca_certificate_file']);

            $def = $container->getDefinition(TlsStream::class);
            $def->replaceArgument(0, $config['tls']['ca_certificate_file']);
            $def->replaceArgument(1, $config['tls']['peer_name']);
            $def->replaceArgument(2, $config['tls']['peer_fingerprint']);
        }

        $def = $container->getDefinition(AdAuthInterface::class);
        $def->replaceArgument(0, $container->getParameter('adauth.host'));
        $def->replaceArgument(3, $container->getParameter('adauth.port'));

        if($options['transport'] === 'tls') {
            $def->replaceArgument(1, new Reference('adauth.transport.tls'));
        } else if($options['transport'] === 'tcp') {
            $def->replaceArgument(1, new Reference('adauth.transport.unencrypted'));
        } else {
            throw new \InvalidArgumentException(sprintf('Invalid transport specified: %s', $options['transport']));
        }

        $def->replaceArgument(2, new Reference($config['serializer']));
    }

    public function resolveOptions(string $url) {
        $options = [
            'transport' => 'tcp',
            'host' => null,
            'port' => 55117
        ];

        $parts = parse_url($url);

        if(isset($parts['scheme'])) {
            $options['transport'] = $parts['scheme'];
        }

        if(isset($parts['host'])) {
            $options['host'] = $parts['host'];
        }

        if(isset($parts['port'])) {
            $options['port'] = $parts['port'];
        }

        return $options;
    }

    public function getAlias() {
        return 'adauth';
    }
}