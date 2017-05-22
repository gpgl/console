<?php

namespace gpgl\console\Commands\Remote;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use gpgl\console\Commands\Traits\DatabaseGateway;

class Defaulter extends Command {
    use DatabaseGateway;

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('remote:default')

            // the short description shown while running "php bin/console list"
            ->setDescription('Get or set default remote')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Returns the default remote, or sets it if alias passed.')

            ->addDatabaseOption()

            ->addOption(
                'alias',
                'a',
                InputOption::VALUE_REQUIRED,
                'Specifying an existing remote will set it as the default'
            )

            ->addOption(
                'unset',
                null,
                InputOption::VALUE_NONE,
                'Removes the default setting for a remote'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dbms = $this->accessDatabase($input, $output);

        if ($input->getOption('unset')) {
            $dbms->remote()->unsetDefault();
            $dbms->export();
            $io = new SymfonyStyle($input, $output);
            return $io->success('Default Remote Removed');
        }

        if (!is_null($alias = $input->getOption('alias'))) {
            $dbms->remote()->default($alias);
            $dbms->export();
            $io = new SymfonyStyle($input, $output);
            return $io->success('Default Remote Saved');
        }

        return $output->writeln($dbms->remote()->whichDefault());
    }
}
