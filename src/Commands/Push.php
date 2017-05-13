<?php

namespace gpgl\console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use gpgl\console\Commands\Traits\DatabaseGateway;

class Push extends Command {
    use DatabaseGateway;

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('push')

            // the short description shown while running "php bin/console list"
            ->setDescription('Push database to server')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Pushes the database to a remote server.')

            ->addDatabaseOption()

            ->addArgument(
                'remote',
                InputArgument::REQUIRED,
                'URL to remote server'
            )

            ->addArgument(
                'token',
                InputArgument::REQUIRED,
                'Authorization token to remote server'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dbms = $this->accessDatabase($input, $output);

        $token = $input->getArgument('token');

        $remote = $input->getArgument('remote');

        $gpgldb = fopen($dbms->getFilename(), 'r');

        $headers = [
            'Authorization' => "Bearer $token",
        ];

        $client = new \GuzzleHttp\Client;

        $response = $client->put($remote, [
            'headers' => $headers,
            'body' => $gpgldb,
        ]);

        $io = new SymfonyStyle($input, $output);

        if ($response->getStatusCode() === 204) {
            return $io->success('push successful');
        }

        return $io->error('push failed');
    }
}
