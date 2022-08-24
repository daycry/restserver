<?php

namespace Daycry\RestServer\Entities;

use CodeIgniter\Entity\Entity;

class ApiEntity extends Entity
{
    use \Tatter\Relations\Traits\EntityTrait;

    protected $table      = 'ws_apis';
    protected $primaryKey = 'id';

    public function __construct(?array $data = null)
    {
        $this->table = config('RestServer')->restApiTable;

        parent::__construct($data);
    }
}