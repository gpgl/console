<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use gpgl\console\Commands\Get;
use gpgl\console\Commands\Set;

class SetTest extends TestCase
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
        $this->database_pw = file_get_contents($this->filename_pw);
        $this->database_pw_deep = file_get_contents($this->filename_pw_deep);
        $this->database_nopw = file_get_contents($this->filename_nopw);
    }

    protected function tearDown()
    {
        putenv('GPGL_DB');
        file_put_contents($this->filename_pw, $this->database_pw);
        file_put_contents($this->filename_pw_deep, $this->database_pw_deep);
        file_put_contents($this->filename_nopw, $this->database_nopw);
    }

    public function test_sets_value()
    {
        $app = new Application;

        $app->add(new Get);
        $command = $app->find('get');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'  => $command->getName(),
            '--database' => $this->filename_nopw,
            'index' => ['two', 'anew'],
        ));

        $output = $commandTester->getDisplay();
        $this->assertNotContains('one', $output);
        $this->assertNotContains('none', $output);
        $this->assertNotContains('nopw', $output);
        $this->assertNotContains('two', $output);
        $this->assertNotContains('nada', $output);
        $this->assertContains('null', $output);

        $app->add(new Set);
        $command = $app->find('set');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'  => $command->getName(),
            '--database' => $this->filename_nopw,
            'value' => 'fdsa',
            'index' => ['two', 'anew'],
        ));
        $output = $commandTester->getDisplay();
        $this->assertContains('Value Saved', $output);

        $app->add(new Get);
        $command = $app->find('get');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'  => $command->getName(),
            '--database' => $this->filename_nopw,
            'index' => ['two', 'anew'],
        ));
        $output = $commandTester->getDisplay();
        $this->assertNotContains('one', $output);
        $this->assertNotContains('none', $output);
        $this->assertNotContains('nopw', $output);
        $this->assertNotContains('two', $output);
        $this->assertNotContains('nada', $output);
        $this->assertContains('fdsa', $output);
    }
}
