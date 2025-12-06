<?php

namespace AdAuthBundle\Command;

use AdAuth\AdAuthInterface;
use AdAuth\Credentials;
use AdAuth\SocketException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'adauth:request:auth', description: 'Send an authentication request to the server')]
readonly class AuthRequestCommand {

    public function __construct(
        private AdAUthInterface $adAuth
    ) {

    }

    public function __invoke(InputInterface $input, SymfonyStyle $io): int {
        $helper = new QuestionHelper();

        $question = new Question('Username: ');
        $username = $helper->ask($input, $io, $question);

        $question = new Question('Password: ');
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        $password = $helper->ask($input, $io, $question);

        try {
            $result = $this->adAuth->authenticate(new Credentials($username, $password));
            $io->writeln(json_encode($result));
        } catch (SocketException $exception) {
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}