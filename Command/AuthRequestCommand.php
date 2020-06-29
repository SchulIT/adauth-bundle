<?php

namespace AdAuthBundle\Command;

use AdAuth\AdAuthInterface;
use AdAuth\Credentials;
use AdAuth\SocketException;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class AuthRequestCommand extends Command {

    private $adAuth;
    private $serializer;

    public function __construct(AdAuthInterface $adAuth, SerializerInterface $serializer, string $name = null) {
        parent::__construct($name);

        $this->adAuth = $adAuth;
        $this->serializer = $serializer;
    }

    protected function configure() {
        $this
            ->setName('adauth:request:auth')
            ->setDescription('Send an authentication request to the server');
    }

    public function execute(InputInterface $input, OutputInterface $output) {
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
            $json = $this->serializer->serialize($result, 'json', null);

            $output->writeln($json);
        } catch (SocketException $exception) {
            $this->getApplication()->renderThrowable($exception, $output);
            return 1;
        }

        return 0;
    }
}