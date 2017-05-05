<?php

namespace gpgl\console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use gpgl\console\Commands\Traits\DatabaseGateway;
use gpgl\console\Commands\Traits\IndexArgument;

class Set extends Command {
    use DatabaseGateway;
    use IndexArgument;

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('set')

            // the short description shown while running "php bin/console list"
            ->setDescription('Stores a value')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Saves a value in the locker under a given index.')

            ->addDatabaseOption()

            ->addArgument(
                'value',
                InputArgument::REQUIRED,
                'Value to store'
            )

            ->addIndexArgument()
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dbms = $this->accessDatabase($input, $output);

        $value = $input->getArgument('value');
        $at = $input->getArgument('index');

        $io = new SymfonyStyle($input, $output);

        if (!empty($dbms->get(...$at))) {
            $question = "There is already a value saved under this index.\n ";
            $question .= 'Are you sure you want to overwrite?';
            if (!$io->confirm($question, false)) {
                return $io->note('Value not saved.');;
            }
        }

        $dbms->set($value, ...$at)->export();

        $io->success('Value Saved');
    }
}
