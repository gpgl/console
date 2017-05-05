<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use gpgl\console\Commands\Get;

class GetTest extends TestCase
{
    protected $filename_pw = __DIR__.'/../fixtures/pw.gpgldb';
    protected $filename_pw_deep = __DIR__.'/../fixtures/pw.deep.gpgldb';
    protected $database_pw;
    protected $database_pw_deep;
    protected $key_pw = 'jeff@example.com';
    protected $password = 'password';

    protected $filename_nopw = __DIR__.'/../fixtures/nopw.gpgldb';
    protected $database_nopw;
    protected $key_nopw = 'nopassword@example.com';

    protected function setUp()
    {
        putenv('GPGL_DB');
    }

    public function test_gets_value()
    {
        $app = new Application;
        $app->add(new Get);

        $command = $app->find('get');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'  => $command->getName(),
            '--database' => $this->filename_nopw,
            'index' => ['two', 'username'],
        ));

        $output = $commandTester->getDisplay();
        $this->assertNotContains('one', $output);
        $this->assertNotContains('none', $output);
        $this->assertNotContains('nopw', $output);
        $this->assertNotContains('two', $output);
        $this->assertNotContains('nada', $output);
        $this->assertContains('nill', $output);
    }

    public function test_gets_array_value()
    {
        $app = new Application;
        $app->add(new Get);

        $command = $app->find('get');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'  => $command->getName(),
            '--database' => $this->filename_nopw,
            'index' => ['two'],
        ));

        $expected = '{
            "username": "nill",
            "password": "nada"
        }';

        $output = $commandTester->getDisplay();
        $this->assertJsonStringEqualsJsonString($expected, $output);
    }
}
