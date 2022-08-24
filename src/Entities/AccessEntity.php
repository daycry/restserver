<?php

namespace Daycry\RestServer\Entities;

use CodeIgniter\Entity\Entity;

class AccessEntity extends Entity
{
    use \Tatter\Relations\Traits\EntityTrait;

    protected $table      = 'ws_access';
    protected $primaryKey = 'id';

    public function __construct(?array $data = null)
    {
        $this->table = config('RestServer')->restAccessTable;

        parent::__construct($data);
    }
}