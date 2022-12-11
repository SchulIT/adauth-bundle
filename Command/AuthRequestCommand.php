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

#[AsCommand(name: 'adauth:request:auth', description: 'Send an authentication request to the server')]
class AuthRequestCommand extends Command {

    public function __construct(private readonly AdAuthInterface $adAuth, string $name = null) {
        parent::__construct($name);
    }

    public function execute(InputInterface $input, OutputInterface $output): int {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $question = new Question('Username: ');
        $username = $helper->ask($input, $output, $question);

        $question = new Question('Password: ');
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        $password = $helper->ask($input, $output, $question);

        try {
            $result = $this->adAuth->authenticate(new Credentials($username, $password));
            $output->writeln(json_encode($result));
        } catch (SocketException $exception) {
            $this->getApplication()->renderThrowable($exception, $output);
            return 1;
        }

        return 0;
    }
}