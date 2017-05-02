<?php

namespace gpgl\console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Output\OutputInterface;
use gpgl\core\DatabaseManagementSystem;
use Crypt_GPG_BadPassphraseException;

class Index extends Command {
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('index')

            // the short description shown while running "php bin/console list"
            ->setDescription('Lists names of stored values')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This shows the index of names for all values saved.')

            ->addOption(
                'database',
                'd',
                InputOption::VALUE_REQUIRED,
                'Filename for database',
                getenv('GPGL_DB') ?: getenv('HOME').'/.gpgldb'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $db = $input->getOption('database');

        try {
            $dbms = new DatabaseManagementSystem($db);
        }

        catch (Crypt_GPG_BadPassphraseException $ex) {
            $helper = $this->getHelper('question');

            $question = new Question('Please enter your password: ');
            $question->setHidden(true);

            $password = $helper->ask($input, $output, $question);

            $dbms = new DatabaseManagementSystem($db, $password);
        }

        $index = implode(PHP_EOL, $dbms->index());
        $output->writeln("<info>$index</info>");
    }
}
