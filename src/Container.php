<?php

namespace gpgl\console;

use gpgl\core\DatabaseManagementSystem;

class Container
{
    protected static $dbms;

    public static function getDbms()
    {
        return static::$dbms;
    }

    public static function setDbms(DatabaseManagementSystem $dbms)
    {
        return static::$dbms = $dbms;
    }

    public static function unsetDbms()
    {
        return static::$dbms = null;
    }
}
