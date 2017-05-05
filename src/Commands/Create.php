<?php

namespace gpgl\console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use gpgl\console\Commands\Traits\DatabaseGateway;
use gpgl\core\DatabaseManagementSystem;
use Crypt_GPG_BadPassphraseException;

class Create extends Command {
    use DatabaseGateway;

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('new')

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
                $this->askPassword($input, $output);
            }
        }

        catch (\Exception $e) {
            unlink(realpath($db));
            throw $e;
        }
    }
}
