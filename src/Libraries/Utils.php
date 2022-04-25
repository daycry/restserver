<?php

namespace Daycry\RestServer\Libraries;

class Utils
{
    public static function modelAliases(object $data, string $field, string $alias)
    {
        if (property_exists($data, $field)) {
            if ($alias) {
                $temp = $data->$field;
                unset($data->{$field});
                $data->{$alias} = $temp;
            }
        }

        return $data;
    }
}
