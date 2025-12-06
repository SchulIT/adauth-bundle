<?php

namespace AdAuthBundle\Command;

use AdAuth\AdAuthInterface;
use AdAuth\SocketException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'adauth:request:ping', description: 'Send a ping request to the server')]
readonly class PingRequestCommand {

    public function __construct(
        private AdAuthInterface $adAuth
    ) { }

    public function execute(SymfonyStyle $io): int {
        try {
            $result = $this->adAuth->ping();
            $io->writeln(json_encode($result));
        } catch (SocketException $exception) {
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}