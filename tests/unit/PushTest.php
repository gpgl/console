<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use gpgl\console\Commands\Push;
use gpgl\console\Container;

class PushTest extends TestCase
{
    protected $filename_nopw = __DIR__.'/../fixtures/nopw.gpgldb';
    protected $database_nopw;
    protected $key_nopw = 'nopassword@example.com';

    protected $remote = 'http://127.0.0.1:8000/api/v1/databases/1';
    protected $token = '';

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

    public function test_pushes_value()
    {
        $app = new Application;

        $app->add(new Push);
        $command = $app->find('push');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'  => $command->getName(),
            '--database' => $this->filename_nopw,
            '--remote' => $this->remote,
            '--token' => $this->token,
        ));

        $output = $commandTester->getDisplay();
        $this->assertNotContains('push failed', $output);
        $this->assertContains('push successful', $output);
    }
}
