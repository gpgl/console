<?php

namespace gpgl\console\Commands\Remote;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use gpgl\console\Commands\Traits\DatabaseGateway;

class Set extends Command {
    use DatabaseGateway;

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('remote:set')

            // the short description shown while running "php bin/console list"
            ->setDescription('Set remote credentials')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Sets alias for URL and token to a given remote.')

            ->addDatabaseOption()

            ->addArgument(
                'alias',
                InputArgument::REQUIRED,
                'Name under which to store remote credentials'
            )

            ->addArgument(
                'url',
                InputArgument::REQUIRED,
                'Remote URL endpoint'
            )

            ->addArgument(
                'token',
                InputArgument::REQUIRED,
                'OAuth Token'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dbms = $this->accessDatabase($input, $output);

        $alias = $input->getArgument('alias');
        $url = $input->getArgument('url');
        $token = $input->getArgument('token');

        $dbms->remote()->set([
            $alias => [
                'url' => $url,
                'token' => $token,
            ],
        ]);
        $dbms->export();

        $io = new SymfonyStyle($input, $output);

        return $io->success('Remote Saved');
    }
}
