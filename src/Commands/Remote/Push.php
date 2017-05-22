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
use GuzzleHttp\Client as Guzzle;

class Push extends Command {
    use DatabaseGateway;

    protected $io;
    protected $client;

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
        $this->io = new SymfonyStyle($input, $output);

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

        $this->client = new Guzzle;

        $response = $this->client->get($url, [
            'headers' => $headers,
            'allow_redirects' => false,
        ]);

        if ($response->getStatusCode() === 204) {
            return $this->put($dbms, $url, $headers);
        }

        if ($response->getStatusCode() !== 200) {
            return $this->io->error('push failed. could not fetch remote.');
        }

        $evaluation = $this->evaluate($dbms, $response->getBody());
        if ( true !== $evaluation ) {
            return;
        }

        return $this->put($dbms, $url, $headers);
    }

    protected function evaluate(DatabaseManagementSystem $dbms, string $remote)
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
                return $this->io->note([
                    'remote is same',
                    'nothing to push',
                ]);
            case History::CHILD:
                return $this->io->note([
                    'remote is child',
                    'nothing to push',
                    'did you mean to pull?',
                ]);
            case History::DIVERGED:
                return $this->io->error([
                    'remote is diverged',
                    'aborting',
                ]);
            case History::UNRELATED:
                return $this->io->error([
                    'remote is unrelated',
                    'aborting',
                ]);
        }
    }

    protected function put(DatabaseManagementSystem $dbms, string $url, array $headers)
    {
        $gpgldb = fopen($dbms->getFilename(), 'r');
        $response = $this->client->put($url, [
            'headers' => $headers,
            'allow_redirects' => false,
            'body' => $gpgldb,
        ]);

        if ($response->getStatusCode() === 204) {
            return $this->io->success('push successful');
        }

        return $this->io->error('push failed');
    }
}
