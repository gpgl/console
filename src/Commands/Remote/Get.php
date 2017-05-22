<?php

namespace gpgl\console\Commands\Remote;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use gpgl\console\Commands\Traits\DatabaseGateway;

class Get extends Command {
    use DatabaseGateway;

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('remote:get')

            // the short description shown while running "php bin/console list"
            ->setDescription('Get remote credentials')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Lists remote aliases or URL and token for a given remote.')

            ->addDatabaseOption()

            ->addArgument(
                'alias',
                InputArgument::OPTIONAL,
                'Specific remote for which to return credentials'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dbms = $this->accessDatabase($input, $output);

        $alias = $input->getArgument('alias');

        if (empty($alias)) {
            $remote = $dbms->remote();
        } else {
            $remote = $dbms->remote()->get($alias);
        }

        return $output->writeln(json_encode($remote, JSON_PRETTY_PRINT));
    }
}
