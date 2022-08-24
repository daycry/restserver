<?php

namespace Daycry\RestServer\Entities;

use CodeIgniter\Entity\Entity;

class NamespaceEntity extends Entity 
{
    use \Tatter\Relations\Traits\EntityTrait;

    protected $table      = 'ws_namespace';
    protected $primaryKey = 'id';

    public function __construct(?array $data = null)
    {
        $this->table = config('RestServer')->restNamespaceTable;

        parent::__construct($data);
    }

    public function setController(string $controller)
    {
        if(mb_substr($controller, 0, 1) !== '\\')
        {
            $this->attributes['controller'] = '\\' . $controller;
        }else{
            $this->attributes['controller'] = $controller;
        }

        return $this;
    }

    public function setMethods(array $methods = [])
    {
        $this->attributes['methods'] = \json_encode($methods);

        return $this;
    }
}