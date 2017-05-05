<?php

namespace gpgl\console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use gpgl\console\Commands\Traits\DatabaseGateway;
use gpgl\console\Commands\Traits\IndexArgument;

class Delete extends Command {
    use DatabaseGateway;
    use IndexArgument;

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('delete')

            // the short description shown while running "php bin/console list"
            ->setDescription('Deletes a value')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Deletes the value in locker stored under a given index.')

            ->addDatabaseOption()

            ->addIndexArgument()
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dbms = $this->accessDatabase($input, $output);

        $at = $input->getArgument('index');

        $io = new SymfonyStyle($input, $output);

        if (empty($dbms->get(...$at))) {
            return $io->error('Value does not exist. Cannot delete.');;
        }

        $question = 'This cannot be undone. Are you sure you want to delete?';
        if (!$io->confirm($question, false)) {
            return $io->note('No changes.');;
        }

        $dbms->delete(...$at)->export();

        $io->success('Value Deleted');
    }
}
