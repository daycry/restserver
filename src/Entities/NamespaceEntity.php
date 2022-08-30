<?php

namespace Daycry\RestServer\Entities;

use CodeIgniter\Entity\Entity;

use Tatter\Relations\Traits\EntityTrait;
use Daycry\RestServer\Traits\Schema;

class NamespaceEntity extends Entity
{
    use EntityTrait;
    use Schema;

    protected $DBGroup = 'default';
    protected $table      = 'ws_namespace';
    protected $primaryKey = 'id';

    protected $casts = [
        'methods' => 'json'
    ];

    public function __construct(?array $data = null)
    {
        $this->DBGroup = config('RestServer')->restDatabaseGroup;
        $this->table = config('RestServer')->restNamespaceTable;

        parent::__construct($data);

        $this->schema = self::getSchema();
    }

    public function setController(string $controller)
    {
        if (mb_substr($controller, 0, 1) !== '\\') {
            $this->attributes['controller'] = '\\' . $controller;
        } else {
            $this->attributes['controller'] = $controller;
        }

        return $this;
    }

    /*public function setMethods(array $methods = [])
    {
        $this->attributes['methods'] = \json_encode($methods);

        return $this;
    }*/
}
