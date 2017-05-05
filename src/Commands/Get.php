<?php

namespace gpgl\console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use gpgl\console\Commands\Traits\DatabaseGateway;
use gpgl\console\Commands\Traits\IndexArgument;

class Get extends Command {
    use DatabaseGateway;
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
        $dbms = $this->accessDatabase($input, $output);

        $from = $input->getArgument('index');

        $value = $dbms->get(...$from);

        $io = new SymfonyStyle($input, $output);

        if (is_null($value)) {
            return $io->error('Value is null.');
        }

        if (is_array($value)) {
            $value = json_encode($value, JSON_PRETTY_PRINT);
        }

        $output->writeln($value);
    }
}
