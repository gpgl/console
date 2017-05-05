<?php

namespace gpgl\console\Commands\Traits;

use Symfony\Component\Console\Input\InputArgument;

trait IndexArgument
{
    protected function addIndexArgument()
    {
        return $this->addArgument(
            'index',
            InputArgument::IS_ARRAY | InputArgument::REQUIRED,
            'Index under which to store and retrieve a value'
        );
    }
}
