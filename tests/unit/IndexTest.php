<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use gpgl\console\Commands\Index;

class IndexTest extends TestCase
{
    protected $filename_pw = __DIR__.'/../fixtures/pw.gpgldb';
    protected $database_pw;
    protected $key_pw = 'jeff@example.com';
    protected $password = 'password';

    protected $filename_nopw = __DIR__.'/../fixtures/nopw.gpgldb';
    protected $database_nopw;
    protected $key_nopw = 'nopassword@example.com';

    protected function setUp()
    {
        putenv('GPGL_DB');
        $this->database_pw = file_get_contents($this->filename_pw);
        $this->database_nopw = file_get_contents($this->filename_nopw);
    }

    protected function tearDown()
    {
        putenv('GPGL_DB');
        file_put_contents($this->filename_pw, $this->database_pw);
        file_put_contents($this->filename_nopw, $this->database_nopw);
    }

    public function test_shows_index_nopw_from_env()
    {
        putenv("GPGL_DB={$this->filename_nopw}");
        $app = new Application;
        $app->add(new Index);

        $command = $app->find('index');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'  => $command->getName(),
        ));

        $output = $commandTester->getDisplay();
        $this->assertContains('one', $output);
        $this->assertNotContains('nopw', $output);
        $this->assertContains('two', $output);
        $this->assertNotContains('nada', $output);
    }

    public function test_shows_index_nopw()
    {
        $app = new Application;
        $app->add(new Index);

        $command = $app->find('index');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'  => $command->getName(),
            '--database' => $this->filename_nopw,
        ));

        $output = $commandTester->getDisplay();
        $this->assertContains('one', $output);
        $this->assertNotContains('nopw', $output);
        $this->assertContains('two', $output);
        $this->assertNotContains('nada', $output);
    }

    public function test_shows_index_pw()
    {
        $app = new Application;
        $app->add(new Index);

        $command = $app->find('index');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs([$this->password]);
        $commandTester->execute(array(
            'command'  => $command->getName(),
            '--database' => $this->filename_pw,
        ));

        $output = $commandTester->getDisplay();
        $this->assertContains('first', $output);
        $this->assertContains('second', $output);
        $this->assertNotContains('P@55', $output);
    }

    /**
     * @expectedException Crypt_GPG_BadPassphraseException
     */
    public function test_rejects_bad_password()
    {
        $app = new Application;
        $app->add(new Index);

        $command = $app->find('index');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['bad password']);
        $commandTester->execute(array(
            'command'  => $command->getName(),
            '--database' => $this->filename_pw,
        ));
    }
}
