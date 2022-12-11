<?php

namespace AdAuthBundle\Command;

use AdAuth\AdAuthInterface;
use AdAuth\SocketException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'adauth:request:ping', description: 'Send a ping request to the server')]
class PingRequestCommand extends Command {

    public function __construct(private readonly AdAuthInterface $adAuth, string $name = null) {
        parent::__construct($name);
    }

    public function execute(InputInterface $input, OutputInterface $output): int {
        try {
            $result = $this->adAuth->ping();
            $output->writeln(json_encode($result));
        } catch (SocketException $exception) {
            $this->getApplication()->renderThrowable($exception, $output);
            return 1;
        }

        return 0;
    }
}