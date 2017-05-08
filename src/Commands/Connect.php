<?php

namespace gpgl\console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use gpgl\console\Commands\Traits\DatabaseGateway;

class Connect extends Command {
    use DatabaseGateway;

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('connect')

            // the short description shown while running "php bin/console list"
            ->setDescription('Run the console interactively')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Establishes a session wherein changes are not automatically saved.')

            ->addDatabaseOption()
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dbms = $this->accessDatabase($input, $output);

        $helper = $this->getHelper('question');

        $commands = [
            'index',
            'get',
            'set',
            'delete',
            'save',
            'quit',
        ];

        $prompt = 'gpgl> ';
        $prompt = new Question($prompt, 'quit');
        $prompt->setAutocompleterValues($commands);

        while ('quit' !== $exec = $helper->ask($input, $output, $prompt)) {
            $command = $this->getApplication()->find(strtok($exec, " "));
            $command->run(new StringInput($exec), $output);
        }
    }
}
