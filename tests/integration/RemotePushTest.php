<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use gpgl\console\Commands\Remote\Push;
use gpgl\console\Container;

class RemotePushTest extends TestCase
{
    protected $filename_pw_deep = __DIR__.'/../fixtures/pw.deep.gpgldb';
    protected $database_pw_deep;
    protected $key_pw = 'jeff@example.com';
    protected $password = 'password';

    protected $filename_nopw = __DIR__.'/../fixtures/nopw.gpgldb';
    protected $database_nopw;
    protected $key_nopw = 'nopassword@example.com';

    protected $url = 'http://127.0.0.1:8000/api/v1/databases/2';
    protected $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImJlMmE0MDgzZDdmY2M1NzE2ZGE2NWNmMDdiYjEwZTc2NGE2MTEyNWYwYjFjZjBiYzQ3NjAxNGY2M2VmNDJhYjU5NDBhMGMzNGI3MDViMmFhIn0.eyJhdWQiOiIxIiwianRpIjoiYmUyYTQwODNkN2ZjYzU3MTZkYTY1Y2YwN2JiMTBlNzY0YTYxMTI1ZjBiMWNmMGJjNDc2MDE0ZjYzZWY0MmFiNTk0MGEwYzM0YjcwNWIyYWEiLCJpYXQiOjE0OTQ5NDkyMDAsIm5iZiI6MTQ5NDk0OTIwMCwiZXhwIjoxNTI2NDg1MjAwLCJzdWIiOiIyIiwic2NvcGVzIjpbXX0.huKHwDsA00owcKN_QnNcrenxOMFE2ix_1-39HNzmNE5k1KyURH9Lum0UNqlPmii9NCJU-YxA2r4BsCsw1nXBTJnxbX8j_wGvd5rjU2wd05VYV33uhRTdx8Sa2RoA30WiDr7ujRHY8U2kN9nb5bPbGJjzphi2vcnmWxopfFdkNFlMWFbguHMajXkuYo1rOTk1iTG2pcS-sw1UTRAZfoSC5qrS1bU-pew7e4EyyQEePTv5ILfq0l-fyA88QG7RNa_ZVPapjjxGPoJrfDQYwnu-rjgpZ5vKR4SUvJ_33rAxYFRAzFlZ8wmykjX20elfDLhiDE-bXPYUq1McxspkoM2OvNrB6vGlgz-HHuW2mASK6wZoSRYDk2sAU0QWHDI84235Je0u6kcls_MrQOWbhTtmbTEUD6YkiJuHOQpSRTXsw58dK7TjE-jmDL9mDf071lP0XGfRiPuhxptYlRpUUUMLmcvtMuL6fnrhHdyGi2qppxiApT2_Fcah-RKawMS6OJ8nUcL5C8S1KLXpaIA_gMmiwtm7ygKOQ3C7-1ZqexzDG4Sq76dMw3HeT0fDylJHalEbhLQAHMrmUuOLY7dwbzjA60l9hVhSzlQQVRMZYUqvjjthj1fkiliSLWPcDT8Y6ixMp3i6xk3shehnxX5wK7pqEspjbB9bXR5A7VdeXOJVL3I';

    protected function setUp()
    {
        putenv('GPGL_DB');
        $this->database_nopw = file_get_contents($this->filename_nopw);
        $this->database_pw_deep = file_get_contents($this->filename_pw_deep);
        Container::unsetDbms();
    }

    protected function tearDown()
    {
        putenv('GPGL_DB');
        file_put_contents($this->filename_nopw, $this->database_nopw);
        file_put_contents($this->filename_pw_deep, $this->database_pw_deep);
        Container::unsetDbms();
    }

    public function test_rejects_token()
    {
        $app = new Application;

        $app->add(new Push);
        $command = $app->find('remote:push');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'  => $command->getName(),
            '--database' => $this->filename_nopw,
            '--url' => $this->url,
            '--token' => 'something',
        ));

        $output = $commandTester->getDisplay();
        $this->assertContains('push failed', $output);
        $this->assertNotContains('push successful', $output);
    }

    public function test_pushes_default()
    {
        $app = new Application;

        $app->add(new Push);
        $command = $app->find('remote:push');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs([$this->password]);
        $commandTester->execute(array(
            'command'  => $command->getName(),
            '--database' => $this->filename_pw_deep,
        ));

        $output = $commandTester->getDisplay();
        $this->assertNotContains('push failed', $output);
        $this->assertContains('remote is same', $output);
    }

    public function test_pushes_new_database()
    {
        $url = 'http://127.0.0.1:8000/api/v1/databases/8';
        $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImUwNjdkNGJhNzRjZmRiYjJlM2RjZjg3NzFjM2ZjOTUwYmY1NDI5Y2MyMzg3MDJkYTIwOWZmMjE2YzIwNDhkYjY1NzYxM2MxMmIxMDc0OGIyIn0.eyJhdWQiOiIxIiwianRpIjoiZTA2N2Q0YmE3NGNmZGJiMmUzZGNmODc3MWMzZmM5NTBiZjU0MjljYzIzODcwMmRhMjA5ZmYyMTZjMjA0OGRiNjU3NjEzYzEyYjEwNzQ4YjIiLCJpYXQiOjE0OTQ4ODI5NTQsIm5iZiI6MTQ5NDg4Mjk1NCwiZXhwIjoxNTI2NDE4OTU0LCJzdWIiOiIxIiwic2NvcGVzIjpbXX0.EyDLmPyoDyVm1OOg6x_LcRMuP2AOq8hA4kX9ZTRN93UxgsQNBJHrzYbs8bjO5jqlh0ljCoSFUO2CPo0BtSLeVBHof4M84I1RsacSdkz6NdwUnob5eyAcU4rtLfFNRyVeCa0lZJSMEYEgX0RrSW62gVcdba8vzJSTYTYm9VCV9LQrt13mOlMhUP0MqRmXQEmmfjQ9zaIjQwNsJn1DiUVF_iOPdtDsCF59ZUScO2FpzU8ilgO8UIyYbLgo2l9nWQNlqJliHuUfv8aPvS0P99LXJr5Kbfp5aMjbMKys5QjW0aroaNYuZiO7vTNpxXIezACkDZCY0xuozqJZL2QSKyhj2CKK4IbW0Z6xE-3kLl3pQWEy1w6txr2aId4CvCdOMKCHT2uExFBQib5oMAOUd7Xv-mNuzP6mcQfvhmj55itsHM1Jr7eoRu8KNTbZZWxNB72WLIzf35ScnHv0HuoZ3V2FnKTy10vmZ707-tGj59j1-llfEqP2DLNE1LaIxmDvWNYfwUcNuRzBy7OKtUaknCzfRsoTV7kUrJKgSkTRHrqq6dVa1BWCGG_zZgmK6fJmimTlmc4E6ccX9BIAPSnLJxErM4T1kV4iS8pbXq8ULXg4X3Jo01tCtX0O1stCpEkDNMZFDFc04Frc4NU6TX_vM-NxKiM-RBNecKE8KMfj6RU-XoE';

        $app = new Application;
        $app->add(new Push);
        $command = $app->find('remote:push');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs([$this->password]);
        $commandTester->execute(array(
            'command'  => $command->getName(),
            '--database' => $this->filename_pw_deep,
            '--url' => $url,
            '--token' => $token,
        ));

        $output = $commandTester->getDisplay();
        $this->assertNotContains('push failed', $output);
        $this->assertContains('push successful', $output);
    }
}
