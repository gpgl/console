<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use gpgl\console\Commands\Get;
use gpgl\console\Commands\Set;
use gpgl\console\Commands\Delete;

class DeleteTest extends TestCase
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

    public function test_deletes_value()
    {
        $app = new Application;

        // make sure it doesn't already exist
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

        // set the value
        $app->add(new Set);
        $command = $app->find('set');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'  => $command->getName(),
            '--database' => $this->filename_nopw,
            'value' => 'to be deleted',
            'index' => ['two', 'anew'],
        ));
        $output = $commandTester->getDisplay();
        $this->assertContains('Value Saved', $output);

        // verify set
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
        $this->assertContains('to be deleted', $output);

        // delete cancel
        $app->add(new Delete);
        $command = $app->find('delete');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(array('no'));
        $commandTester->execute(array(
            'command'  => $command->getName(),
            '--database' => $this->filename_nopw,
            'index' => ['two', 'anew'],
        ));
        $output = $commandTester->getDisplay();
        $this->assertContains('Are you sure you want to delete?', $output);
        $this->assertNotContains('Value Deleted', $output);

        // verify cancel
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
        $this->assertContains('to be deleted', $output);

        // delete confirm
        $app->add(new Delete);
        $command = $app->find('delete');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(array('yes'));
        $commandTester->execute(array(
            'command'  => $command->getName(),
            '--database' => $this->filename_nopw,
            'index' => ['two', 'anew'],
        ));
        $output = $commandTester->getDisplay();
        $this->assertContains('Are you sure you want to delete?', $output);
        $this->assertContains('Value Deleted', $output);

        // verify delete
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
    }

    public function test_delete_errors_on_non_existent_value()
    {
        $app = new Application;

        // make sure it doesn't already exist
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

        // delete confirm
        $app->add(new Delete);
        $command = $app->find('delete');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(array('yes'));
        $commandTester->execute(array(
            'command'  => $command->getName(),
            '--database' => $this->filename_nopw,
            'index' => ['two', 'anew'],
        ));
        $output = $commandTester->getDisplay();
        $this->assertNotContains('Are you sure you want to delete?', $output);
        $this->assertContains('Value does not exist. Cannot delete.', $output);
    }
}
