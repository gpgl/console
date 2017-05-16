<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use gpgl\console\Commands\Remote\Defaulter;
use gpgl\console\Container;

class RemoteDefaulterTest extends TestCase
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

    /**
     * @expectedException \gpgl\core\Exceptions\MissingRemote
     */
    public function test_gets_missing_default()
    {
        $app = new Application;
        $app->add(new Defaulter);
        $command = $app->find('remote:default');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'  => $command->getName(),
            '--database' => $this->filename_nopw,
        ));

        $this->assertTrue(false);
    }

    public function test_sets_default()
    {
        $app = new Application;
        $app->add(new Defaulter);
        $command = $app->find('remote:default');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'  => $command->getName(),
            '--database' => $this->filename_nopw,
            '--alias' => 'origin'
        ));

        $output = $commandTester->getDisplay();
        $this->assertContains('Default Remote Saved', $output);

        Container::unsetDbms();

        $app = new Application;
        $app->add(new Defaulter);
        $command = $app->find('remote:default');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'  => $command->getName(),
            '--database' => $this->filename_nopw,
        ));

        $output = $commandTester->getDisplay();
        $this->assertContains('origin', $output);
    }
}
