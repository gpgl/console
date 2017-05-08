<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use gpgl\console\Commands\Create;
use gpgl\console\Container;

class CreateTest extends TestCase
{
    protected $filename_pw = __DIR__.'/../fixtures/create-pw.gpgldb';
    protected $key_pw = 'jeff@example.com';
    protected $password = 'password';

    protected $filename_nopw = __DIR__.'/../fixtures/create-nopw.gpgldb';
    protected $key_nopw = 'nopassword@example.com';

    protected function setUp()
    {
        putenv('GPGL_DB');

        if (file_exists($this->filename_pw)) {
            unlink(realpath($this->filename_pw));
        }

        if (file_exists($this->filename_nopw)) {
            unlink(realpath($this->filename_nopw));
        }

        Container::unsetDbms();
    }

    protected function tearDown()
    {
        putenv('GPGL_DB');

        if (file_exists($this->filename_pw)) {
            unlink(realpath($this->filename_pw));
        }

        if (file_exists($this->filename_nopw)) {
            unlink(realpath($this->filename_nopw));
        }

        Container::unsetDbms();
    }

    public function test_creates_database_nopw()
    {
        $this->assertFileNotExists($this->filename_nopw);
        $app = new Application;
        $app->add(new Create);

        $command = $app->find('new');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'  => $command->getName(),
            'key' => $this->key_nopw,
            '--database' => $this->filename_nopw,
        ));

        $this->assertFileExists($this->filename_nopw);
    }

    public function test_creates_database_pw()
    {
        $this->assertFileNotExists($this->filename_pw);
        $app = new Application;
        $app->add(new Create);

        $command = $app->find('new');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs([$this->password]);
        $commandTester->execute(array(
            'command'  => $command->getName(),
            'key' => $this->key_pw,
            '--database' => $this->filename_pw,
        ));

        $this->assertFileExists($this->filename_pw);
    }

    /**
     * @expectedException \Exception
     */
    public function test_cleans_up_after_creation_with_bad_password()
    {
        $this->assertFileNotExists($this->filename_pw);
        $app = new Application;
        $app->add(new Create);

        $command = $app->find('new');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['bad password']);

        try {
            $commandTester->execute(array(
                'command'  => $command->getName(),
                'key' => $this->key_pw,
                '--database' => $this->filename_pw,
            ));
            $this->assertTrue(false);
        }

        catch (Exception $e) {
            $this->assertFileNotExists($this->filename_pw);
            throw $e;
        }
    }
}
