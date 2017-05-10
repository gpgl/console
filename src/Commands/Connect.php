<?php

namespace gpgl\console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use gpgl\console\Commands\Traits\DatabaseGateway;

class Connect extends Command {
    use DatabaseGateway;

    protected static $connected = false;
    public static function isConnected() : bool
    {
        return static::$connected;
    }

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('connect')

            // the short description shown while running "php bin/console list"
            ->setDescription('Run the console interactively')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Establishes a session wherein changes are not automatically saved.')

            ->addDatabaseOption()
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dbms = $this->accessDatabase($input, $output);
        static::$connected = true;

        $output->writeln($this->getApplication()->getLongVersion().PHP_EOL);

        $helper = $this->getHelper('question');

        $commands = array_merge([
            'index',
            'get',
            'set',
            'delete',
            'quit',
        ], $this->getAutocompletedCommands($dbms->index(0)));

        $prompt = '<comment>gpgl> </comment>';
        $prompt = new Question($prompt, 'quit');
        $prompt->setAutocompleterValues($commands);

        while ('quit' !== $exec = $helper->ask($input, $output, $prompt)) {
            $command = $this->getApplication()->find(strtok($exec, " "));
            $output->writeln('');
            $command->run(new StringInput($exec), $output);
            $output->writeln('');
        }

        static::$connected = false;
    }

    protected function getRecursiveIndexes(array $indices, string $parent = '') : array
    {
        $indexes = [];

        foreach ($indices as $index => $value) {
            $indexes []= $current = "{$parent}{$index} ";
            if (is_array($value)) {
                $indexes = array_merge($indexes, $this->getRecursiveIndexes($value, $current));
            }
        }

        return $indexes;
    }

    protected function getAutocompletedCommands(array $indices) : array
    {
        $indexes = $this->getRecursiveIndexes($indices);

        // https://stackoverflow.com/a/28115783/4233593
        $get = preg_filter('/^/', 'get ', $indexes);
        $delete = preg_filter('/^/', 'delete ', $indexes);

        return array_merge($get, $delete);
    }
}
