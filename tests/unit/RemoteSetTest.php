<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use gpgl\console\Commands\Remote\{Get,Set};
use gpgl\console\Container;

class RemoteSetTest extends TestCase
{
    protected $filename_nopw = __DIR__.'/../fixtures/nopw.gpgldb';
    protected $database_nopw;
    protected $key_nopw = 'nopassword@example.com';

    protected function setUp()
    {
        putenv('GPGL_DB');
        $this->database_nopw = file_get_contents($this->filename_nopw);
        Container::unsetDbms();
    }

    protected function tearDown()
    {
        putenv('GPGL_DB');
        file_put_contents($this->filename_nopw, $this->database_nopw);
        Container::unsetDbms();
    }

    public function test_sets_remote()
    {
        $expected = [
            'url' => 'https://gpgl.example.org/api/v1/databases/678',
            'token' => random_str(32),
        ];

        $app = new Application;
        $app->add(new Set);
        $command = $app->find('remote:set');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'  => $command->getName(),
            '--database' => $this->filename_nopw,
            'alias' => 'name',
            'url' => $expected['url'],
            'token' => $expected['token'],
        ]);

        Container::unsetDbms();

        $app = new Application;
        $app->add(new Get);
        $command = $app->find('remote:get');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'  => $command->getName(),
            '--database' => $this->filename_nopw,
            'alias' => 'name',
        ]);

        $actual = $commandTester->getDisplay();

        $this->assertJsonStringEqualsJsonString(json_encode($expected), $actual);
    }
}
