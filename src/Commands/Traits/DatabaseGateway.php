<?php

namespace gpgl\console\Commands\Traits;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Output\OutputInterface;
use gpgl\core\DatabaseManagementSystem;
use Crypt_GPG_BadPassphraseException;
use gpgl\console\Container;

trait DatabaseGateway
{
    protected function addDatabaseOption()
    {
        return $this->addOption(
            'database',
            'd',
            InputOption::VALUE_REQUIRED,
            'Filename for database',
            getenv('GPGL_DB') ?: getenv('HOME').'/.gpgldb'
        );
    }

    protected function accessDatabase(InputInterface $input, OutputInterface $output) : DatabaseManagementSystem
    {
        if (is_null(Container::getDbms())) {
            $db = $input->getOption('database');

            try {
                Container::setDbms(new DatabaseManagementSystem($db));
            }

            catch (Crypt_GPG_BadPassphraseException $ex) {
                Container::setDbms($this->askPassword($input, $output));
            }
        }

        return Container::getDbms();
    }

    protected function askPassword(InputInterface $input, OutputInterface $output) : DatabaseManagementSystem
    {
        $db = $input->getOption('database');

        $helper = $this->getHelper('question');

        $question = new Question('Please enter your password: ');
        $question->setHidden(true);

        $password = $helper->ask($input, $output, $question);

        $dbms = new DatabaseManagementSystem($db, $password);

        return $dbms;
    }
}
