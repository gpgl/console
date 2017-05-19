<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use gpgl\console\Commands\Remote\Pull;
use gpgl\console\Container;

class PullScenarioTest extends TestCase
{
    protected $filename_same = __DIR__.'/../fixtures/scenario/same.gpgldb';
    protected $database_same;
    protected $password = 'password';
    protected $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImUwNjdkNGJhNzRjZmRiYjJlM2RjZjg3NzFjM2ZjOTUwYmY1NDI5Y2MyMzg3MDJkYTIwOWZmMjE2YzIwNDhkYjY1NzYxM2MxMmIxMDc0OGIyIn0.eyJhdWQiOiIxIiwianRpIjoiZTA2N2Q0YmE3NGNmZGJiMmUzZGNmODc3MWMzZmM5NTBiZjU0MjljYzIzODcwMmRhMjA5ZmYyMTZjMjA0OGRiNjU3NjEzYzEyYjEwNzQ4YjIiLCJpYXQiOjE0OTQ4ODI5NTQsIm5iZiI6MTQ5NDg4Mjk1NCwiZXhwIjoxNTI2NDE4OTU0LCJzdWIiOiIxIiwic2NvcGVzIjpbXX0.EyDLmPyoDyVm1OOg6x_LcRMuP2AOq8hA4kX9ZTRN93UxgsQNBJHrzYbs8bjO5jqlh0ljCoSFUO2CPo0BtSLeVBHof4M84I1RsacSdkz6NdwUnob5eyAcU4rtLfFNRyVeCa0lZJSMEYEgX0RrSW62gVcdba8vzJSTYTYm9VCV9LQrt13mOlMhUP0MqRmXQEmmfjQ9zaIjQwNsJn1DiUVF_iOPdtDsCF59ZUScO2FpzU8ilgO8UIyYbLgo2l9nWQNlqJliHuUfv8aPvS0P99LXJr5Kbfp5aMjbMKys5QjW0aroaNYuZiO7vTNpxXIezACkDZCY0xuozqJZL2QSKyhj2CKK4IbW0Z6xE-3kLl3pQWEy1w6txr2aId4CvCdOMKCHT2uExFBQib5oMAOUd7Xv-mNuzP6mcQfvhmj55itsHM1Jr7eoRu8KNTbZZWxNB72WLIzf35ScnHv0HuoZ3V2FnKTy10vmZ707-tGj59j1-llfEqP2DLNE1LaIxmDvWNYfwUcNuRzBy7OKtUaknCzfRsoTV7kUrJKgSkTRHrqq6dVa1BWCGG_zZgmK6fJmimTlmc4E6ccX9BIAPSnLJxErM4T1kV4iS8pbXq8ULXg4X3Jo01tCtX0O1stCpEkDNMZFDFc04Frc4NU6TX_vM-NxKiM-RBNecKE8KMfj6RU-XoE";

    protected function setUp()
    {
        putenv('GPGL_DB');
        $this->database_same = file_get_contents($this->filename_same);
        Container::unsetDbms();
    }

    protected function tearDown()
    {
        putenv('GPGL_DB');
        file_put_contents($this->filename_same, $this->database_same);
        Container::unsetDbms();
    }

    public function scenarioDataProvider()
    {
        return [
            'notices-same' => [
                'db' => '3',
                'expects' => 'same',
                'notExpects' => 'pull successful',
            ],
            'notices-parent' => [
                'db' => '4',
                'expects' => 'parent',
                'notExpects' => 'pull successful',
            ],
            'pulls-child' => [
                'db' => '5',
                'expects' => 'pull successful',
                'notExpects' => 'pull failed',
            ],
            'rejects-diverged' => [
                'db' => '6',
                'expects' => 'diverged',
                'notExpects' => 'pull successful',
            ],
            'rejects-unrelated' => [
                'db' => '7',
                'expects' => 'unrelated',
                'notExpects' => 'pull successful',
            ],
        ];
    }

    /**
     * @dataProvider scenarioDataProvider
     */
    public function test_pulls_appropriately($db, $expects, $notExpects)
    {
        $app = new Application;
        $app->add(new Pull);
        $command = $app->find('remote:pull');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs([$this->password]);
        $commandTester->execute(array(
            'command'  => $command->getName(),
            '--database' => $this->filename_same,
            '--url' => "http://127.0.0.1:8000/api/v1/databases/$db",
            '--token' => $this->token,
        ));

        $output = $commandTester->getDisplay();
        $this->assertContains($expects, $output);
        $this->assertNotContains($notExpects, $output);
    }
}
