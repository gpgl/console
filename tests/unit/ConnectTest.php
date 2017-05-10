<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use gpgl\console\Commands\{Get,Connect};
use gpgl\console\Container;

class ConnectTest extends TestCase
{
    protected $filename_nopw = __DIR__.'/../fixtures/nopw.gpgldb';

    protected function setUp()
    {
        putenv('GPGL_DB');
        Container::unsetDbms();
    }

    protected function tearDown()
    {
        putenv('GPGL_DB');
        Container::unsetDbms();
    }

    public function test_connects_with_tab_completion()
    {
        $app = new Application;
        $app->add(new Get);
        $app->add(new Connect);

        $command = $app->find('connect');
        $commandTester = new CommandTester($command);

        // tab completion 'get one password'
        $commandTester->setInputs(["g\t o\tp\t"]);
        $commandTester->execute(array(
            'command'  => $command->getName(),
            '--database' => $this->filename_nopw,
        ));

        $output = $commandTester->getDisplay();
        $this->assertContains('nopw', $output);
        $this->assertNotContains('none', $output);
    }
}
