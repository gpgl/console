<?php

namespace gpgl\console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Output\OutputInterface;
use gpgl\console\Commands\Traits\DatabaseOption;
use gpgl\core\DatabaseManagementSystem;
use Crypt_GPG_BadPassphraseException;

class Create extends Command {
    use DatabaseOption;

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('create')

            // the short description shown while running "php bin/console list"
            ->setDescription('Create a new database')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This creates a new encrypted database saved to disk.')

            ->addDatabaseOption()

            ->addArgument(
                'key',
                InputArgument::REQUIRED,
                'Which private key to use for encryption and decryption?'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $db = $input->getOption('database');
        $key = $input->getArgument('key');

        $dbms = DatabaseManagementSystem::create($db, $key);

        try {
            try {
                $dbms->import();
            }

            catch (Crypt_GPG_BadPassphraseException $ex) {
                $helper = $this->getHelper('question');

                $question = new Question('Please enter your password: ');
                $question->setHidden(true);

                $password = $helper->ask($input, $output, $question);

                $dbms = new DatabaseManagementSystem($db, $password);
            }
        }

        catch (\Exception $e) {
            unlink(realpath($db));
            throw $e;
        }
    }
}
