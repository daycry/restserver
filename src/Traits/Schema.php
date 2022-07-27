<?php

namespace Daycry\RestServer\Traits;

use Tatter\Schemas\Drafter\Handlers\DatabaseHandler;

trait Schema
{
    public static function getSchema()
    {
        $configSchema = config('Schemas');
        $handler = new DatabaseHandler($configSchema, config('RestServer')->restDatabaseGroup);
        return $handler->draft();
    }
}
