<?php

namespace gpgl\console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use gpgl\core\DatabaseManagementSystem;

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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $db = getenv('GPGL_DB');
        $dbms = new DatabaseManagementSystem($db);
        $index = implode(PHP_EOL, $dbms->index());
        $output->writeln("<info>$index</info>");
    }
}
