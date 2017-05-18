<?php

namespace gpgl\console\Commands\Remote;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use gpgl\console\Commands\Traits\DatabaseGateway;
use gpgl\core\DatabaseManagementSystem;
use Crypt_GPG_BadPassphraseException;
use gpgl\console\Container;

class Pull extends Command {
    use DatabaseGateway;

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('remote:pull')

            // the short description shown while running "php bin/console list"
            ->setDescription('Pull database from server')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Pulls the database from a remote server.')

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

        $url = $input->getOption('url');
        $token = $input->getOption('token');

        if (!isset($url, $token)) {
            $remote = $dbms->remote()->default();
            $url = $remote->url();
            $token = $remote->token();
        }

        $headers = [
            'Authorization' => "Bearer $token",
        ];

        $client = new \GuzzleHttp\Client;

        $response = $client->get($url, [
            'headers' => $headers,
        ]);

        $io = new SymfonyStyle($input, $output);

        if ($response->getStatusCode() === 200) {
            file_put_contents($dbms->getFilename(), $response->getBody());

            try {
                Container::setDbms(new DatabaseManagementSystem($dbms->getFilename()));
            }

            catch (Crypt_GPG_BadPassphraseException $ex) {
                Container::setDbms(new DatabaseManagementSystem($dbms->getFilename(), $dbms->getPassword()));
            }

            return $io->success('pull successful');
        }

        return $io->error('pull failed');
    }
}
