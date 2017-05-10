<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use gpgl\console\Commands\{Get,Set,Connect};
use gpgl\console\Container;

class ConnectTest extends TestCase
{
    protected $filename_nopw = __DIR__.'/../fixtures/nopw.gpgldb';

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

    public function test_connects_and_sets_without_warning()
    {
        $app = new Application;
        $app->add(new Set);
        $app->add(new Connect);

        $command = $app->find('connect');
        $commandTester = new CommandTester($command);

        // tab completion 'get one password'
        $commandTester->setInputs(['set value temp']);
        $commandTester->execute(array(
            'command'  => $command->getName(),
            '--database' => $this->filename_nopw,
        ));

        $output = $commandTester->getDisplay();
        $this->assertContains('[OK] Value Saved', $output);
        $this->assertNotContains(
            'WARNING: Using this command may save sensitive values in plain text',
            $output
        );
    }
}
