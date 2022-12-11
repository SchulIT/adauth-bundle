<?php

namespace AdAuthBundle;

use AdAuth\AdAuth;
use AdAuth\Stream\TlsStream;

class AdAuthFactory {

    public static function createAdAuth(string $url, array $params): AdAuth {
        $options = static::resolveOptions($url);
        $stream = new TlsStream($params['ca_certificate_file'], $params['peer_name'], $params['peer_fingerprint']);

        return new AdAuth($options['host'], $stream, $options['port']);
    }

    private static function resolveOptions(string $url): array {
        $options = [
            'transport' => 'tls',
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
}