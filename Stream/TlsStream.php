<?php

namespace AdAuthBundle\Stream;

use AdAuth\SocketConnectException;
use AdAuth\Stream\StreamInterface;

class TlsStream implements StreamInterface {

    private $peerName;
    private $serialNumber;
    private $caFile;

    public function __construct($peerName, $serialNumber, $caFile) {
        $this->peerName = $peerName;
        $this->serialNumber = $serialNumber;

        if(!file_exists($caFile)) {
            throw new \InvalidArgumentException(sprintf('CA certificate file "%s" does not exist', $caFile));
        }

        if(!is_readable($caFile)) {
            throw new \InvalidArgumentException(sprintf('CA certificate file "%s" is not readable', $caFile));
        }

        $this->caFile = $caFile;
    }


    /**
     * @inheritDoc
     */
    public function getStream($host, $port) {
        $options = [
            'ssl' => [
                'crypto_method' => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT,
                'disable_compression' => true,
                'peer_name' => $this->peerName,
                'verify_peer' => true,
                'allow_self_signed' => true,
                'cafile' => $this->caFile,
                'peer_fingerprint' => $this->serialNumber,
                'ciphers' => 'ECDHE-RSA-AES256-GCM-SHA384:ECDHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-SHA384:ECDHE-RSA-AES128-SHA256:ECDHE-RSA-AES256-SHA:ECDHE-RSA-AES128-SHA:DHE-RSA-AES256-SHA256:DHE-RSA-AES128-SHA256:DHE-RSA-AES256-SHA:DHE-RSA-AES128-SHA:ECDHE-RSA-DES-CBC3-SHA:EDH-RSA-DES-CBC3-SHA:AES256-GCM-SHA384:AES128-GCM-SHA256:AES256-SHA256:AES128-SHA256:AES256-SHA:AES128-SHA:DES-CBC3-SHA:HIGH:!aNULL:!eNULL:!EXPORT:!DES:!MD5:!PSK:!RC4',
            ]
        ];

        $context = stream_context_create($options);
        $stream = @stream_socket_client('tls://' . $host . ':' . $port, $errno, $errstr, 1, STREAM_CLIENT_CONNECT, $context);

        if(!is_resource($stream)) {
            throw new SocketConnectException(sprintf('Error while connecting: %s (code: %s)', $errstr, $errno));
        }

        return $stream;
    }
}