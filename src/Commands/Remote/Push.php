<?php

namespace gpgl\console\Commands\Remote;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use gpgl\console\Commands\Traits\DatabaseGateway;
use gpgl\core\DatabaseManagementSystem;
use gpgl\core\History;
use Crypt_GPG_BadPassphraseException;
use gpgl\console\Container;

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
            'allow_redirects' => false,
        ]);

        $io = new SymfonyStyle($input, $output);

        if ($response->getStatusCode() !== 200) {
            return $io->error('push failed. could not fetch remote.');
        }

        $evaluation = $this->evaluate($dbms, $response->getBody(), $io);
        if ( true !== $evaluation ) {
            return;
        }

        $gpgldb = fopen($dbms->getFilename(), 'r');
        $response = $client->put($url, [
            'headers' => $headers,
            'allow_redirects' => false,
            'body' => $gpgldb,
        ]);

        if ($response->getStatusCode() === 204) {
            return $io->success('push successful');
        }

        return $io->error('push failed');
    }

    protected function evaluate(DatabaseManagementSystem $dbms, string $remote, SymfonyStyle $io)
    {
        $filename = tempnam(realpath(sys_get_temp_dir()), 'gpgldb_remote_');
        file_put_contents($filename, $remote);
        try {
            $temp = new DatabaseManagementSystem($filename);
        }
        catch (Crypt_GPG_BadPassphraseException $ex) {
            $temp = new DatabaseManagementSystem($filename, $dbms->getPassword());
        }
        finally {
            unlink($filename);
        }

        $base = new History($dbms->history());
        $target = new History($temp->history());
        switch ( History::compare($base, $target) ) {
            case History::PARENT:
                return true;
            case History::SAME:
                return $io->note([
                    'remote is same',
                    'nothing to push',
                ]);
            case History::CHILD:
                return $io->note([
                    'remote is child',
                    'nothing to push',
                    'did you mean to pull?',
                ]);
            case History::DIVERGED:
                return $io->error([
                    'remote is diverged',
                    'aborting',
                ]);
            case History::UNRELATED:
                return $io->error([
                    'remote is unrelated',
                    'aborting',
                ]);
        }
    }
}
