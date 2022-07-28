<?php

namespace Daycry\RestServer\Traits;

use Tatter\Schemas\Drafter\Handlers\DatabaseHandler;

trait Schema
{
    public static function getSchema()
    {
        $cache = \Config\Services::cache();

        if (!$schema = $cache->get('database-schema-' . config('RestServer')->restDatabaseGroup)) {
            $configSchema = config('Schemas');
            $handler = new DatabaseHandler($configSchema, config('RestServer')->restDatabaseGroup);
            $schema = $handler->draft();
            $cache->save('database-schema-' . config('RestServer')->restDatabaseGroup, $schema);
        }

        return $schema;
    }
}
