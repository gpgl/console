<?php

namespace gpgl\console\Commands\Remote;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use gpgl\console\Commands\Traits\DatabaseGateway;

class Push extends Command {
    use DatabaseGateway;

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('remote:push')

            // the short description shown while running "php bin/console list"
            ->setDescription('Push database to server')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Pushes the database to a remote server.')

            ->addDatabaseOption()

            ->addOption(
                'alias',
                'a',
                InputOption::VALUE_REQUIRED,
                'Alias name for remote'
            )

            ->addOption(
                'url',
                'u',
                InputOption::VALUE_REQUIRED,
                'URL to remote server'
            )

            ->addOption(
                'token',
                't',
                InputOption::VALUE_REQUIRED,
                'Authorization token to remote server'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dbms = $this->accessDatabase($input, $output);

        $token = $input->getOption('token');

        $url = $input->getOption('url');

        $gpgldb = fopen($dbms->getFilename(), 'r');

        $headers = [
            'Authorization' => "Bearer $token",
        ];

        $client = new \GuzzleHttp\Client;

        $response = $client->put($url, [
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
