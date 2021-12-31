<?php

namespace AdAuthBundle\Command;

use AdAuth\AdAuthInterface;
use AdAuth\SocketException;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PingRequestCommand extends Command {

    private AdAuthInterface $adAuth;
    private SerializerInterface $serializer;

    public function __construct(AdAuthInterface $adAuth, SerializerInterface $serializer, string $name = null) {
        parent::__construct($name);

        $this->adAuth = $adAuth;
        $this->serializer = $serializer;
    }

    protected function configure() {
        $this
            ->setName('adauth:request:ping')
            ->setDescription('Send a ping request to the server');
    }

    public function execute(InputInterface $input, OutputInterface $output): int {
        try {
            $result = $this->adAuth->ping();

            $json = $this->serializer->serialize($result, 'json', null);

            $output->writeln($json);
        } catch (SocketException $exception) {
            $this->getApplication()->renderThrowable($exception, $output);
            return 1;
        }

        return 0;
    }
}