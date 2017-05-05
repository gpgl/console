<?php

namespace gpgl\console\Commands\Traits;

use Symfony\Component\Console\Input\InputOption;

trait DatabaseOption
{
    protected function addDatabaseOption()
    {
        return $this->addOption(
            'database',
            'd',
            InputOption::VALUE_REQUIRED,
            'Filename for database',
            getenv('GPGL_DB') ?: getenv('HOME').'/.gpgldb'
        );
    }
}
