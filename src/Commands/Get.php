<?php

namespace gpgl\console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Output\OutputInterface;
use gpgl\console\Commands\Traits\DatabaseOption;
use gpgl\console\Commands\Traits\IndexArgument;
use gpgl\core\DatabaseManagementSystem;
use Crypt_GPG_BadPassphraseException;

class Get extends Command {
    use DatabaseOption;
    use IndexArgument;

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('get')

            // the short description shown while running "php bin/console list"
            ->setDescription('Retrieves a stored value')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Gets a value stored in the locker under a given index.')

            ->addDatabaseOption()

            ->addIndexArgument()
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $db = $input->getOption('database');
        $from = $input->getArgument('index');

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

        $value = json_encode($dbms->get(...$from), JSON_PRETTY_PRINT);
        $output->writeln("<info>$value</info>");
    }
}
