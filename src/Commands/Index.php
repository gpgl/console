<?php

namespace gpgl\console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use gpgl\console\Commands\Traits\DatabaseGateway;

class Index extends Command {
    use DatabaseGateway;

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

            ->addDatabaseOption()

            ->addOption(
                'limit',
                'l',
                InputOption::VALUE_REQUIRED,
                'Depth level limit to recursively show indexes',
                1
            )

            ->addArgument(
                'index',
                InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
                'Starting index from which to show sub-indicies'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dbms = $this->accessDatabase($input, $output);

        $limit = $input->getOption('limit');
        $from = $input->getArgument('index');

        $index = json_encode($dbms->index($limit, ...$from), JSON_PRETTY_PRINT);
        $output->writeln("<info>$index</info>");
    }
}
