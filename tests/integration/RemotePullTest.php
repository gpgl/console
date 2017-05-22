<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use gpgl\console\Commands\Remote\Pull;
use gpgl\console\Container;

class RemotePullTest extends TestCase
{
    protected $filename_pw_deep = __DIR__.'/../fixtures/pw.deep.gpgldb';
    protected $database_pw_deep;
    protected $password = 'password';

    protected function setUp()
    {
        putenv('GPGL_DB');
        $this->database_pw_deep = file_get_contents($this->filename_pw_deep);
        Container::unsetDbms();
    }

    protected function tearDown()
    {
        putenv('GPGL_DB');
        file_put_contents($this->filename_pw_deep, $this->database_pw_deep);
        Container::unsetDbms();
    }

    public function test_pulls_default()
    {
        $app = new Application;

        $app->add(new Pull);
        $command = $app->find('remote:pull');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs([$this->password]);
        $commandTester->execute(array(
            'command'  => $command->getName(),
            '--database' => $this->filename_pw_deep,
        ));

        $output = $commandTester->getDisplay();
        $this->assertNotContains('pull failed', $output);
        $this->assertContains('remote is same', $output);
    }
}
