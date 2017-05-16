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

    protected $remote = 'http://127.0.0.1:8000/api/v1/databases/2';
    protected $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImJlMmE0MDgzZDdmY2M1NzE2ZGE2NWNmMDdiYjEwZTc2NGE2MTEyNWYwYjFjZjBiYzQ3NjAxNGY2M2VmNDJhYjU5NDBhMGMzNGI3MDViMmFhIn0.eyJhdWQiOiIxIiwianRpIjoiYmUyYTQwODNkN2ZjYzU3MTZkYTY1Y2YwN2JiMTBlNzY0YTYxMTI1ZjBiMWNmMGJjNDc2MDE0ZjYzZWY0MmFiNTk0MGEwYzM0YjcwNWIyYWEiLCJpYXQiOjE0OTQ5NDkyMDAsIm5iZiI6MTQ5NDk0OTIwMCwiZXhwIjoxNTI2NDg1MjAwLCJzdWIiOiIyIiwic2NvcGVzIjpbXX0.huKHwDsA00owcKN_QnNcrenxOMFE2ix_1-39HNzmNE5k1KyURH9Lum0UNqlPmii9NCJU-YxA2r4BsCsw1nXBTJnxbX8j_wGvd5rjU2wd05VYV33uhRTdx8Sa2RoA30WiDr7ujRHY8U2kN9nb5bPbGJjzphi2vcnmWxopfFdkNFlMWFbguHMajXkuYo1rOTk1iTG2pcS-sw1UTRAZfoSC5qrS1bU-pew7e4EyyQEePTv5ILfq0l-fyA88QG7RNa_ZVPapjjxGPoJrfDQYwnu-rjgpZ5vKR4SUvJ_33rAxYFRAzFlZ8wmykjX20elfDLhiDE-bXPYUq1McxspkoM2OvNrB6vGlgz-HHuW2mASK6wZoSRYDk2sAU0QWHDI84235Je0u6kcls_MrQOWbhTtmbTEUD6YkiJuHOQpSRTXsw58dK7TjE-jmDL9mDf071lP0XGfRiPuhxptYlRpUUUMLmcvtMuL6fnrhHdyGi2qppxiApT2_Fcah-RKawMS6OJ8nUcL5C8S1KLXpaIA_gMmiwtm7ygKOQ3C7-1ZqexzDG4Sq76dMw3HeT0fDylJHalEbhLQAHMrmUuOLY7dwbzjA60l9hVhSzlQQVRMZYUqvjjthj1fkiliSLWPcDT8Y6ixMp3i6xk3shehnxX5wK7pqEspjbB9bXR5A7VdeXOJVL3I';

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

    public function test_rejects_token()
    {
        $app = new Application;

        $app->add(new Push);
        $command = $app->find('push');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'  => $command->getName(),
            '--database' => $this->filename_nopw,
            '--remote' => $this->remote,
            '--token' => 'something',
        ));

        $output = $commandTester->getDisplay();
        $this->assertContains('push failed', $output);
        $this->assertNotContains('push successful', $output);
    }
}
