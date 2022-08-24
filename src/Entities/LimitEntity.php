<?php

namespace Daycry\RestServer\Entities;

use CodeIgniter\Entity\Entity;

class LimitEntity extends Entity
{
    use \Tatter\Relations\Traits\EntityTrait;

    protected $table      = 'ws_limits';
    protected $primaryKey = 'id';

    public function __construct(?array $data = null)
    {
        $this->table = config('RestServer')->restLimitsTable;

        parent::__construct($data);
    }
}