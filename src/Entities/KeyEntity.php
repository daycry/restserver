<?php

namespace Daycry\RestServer\Entities;

use CodeIgniter\Entity\Entity;

class KeyEntity extends Entity
{
    use \Tatter\Relations\Traits\EntityTrait;

    protected $table      = 'ws_keys';
    protected $primaryKey = 'id';

    public function __construct(?array $data = null)
    {
        $this->table = config('RestServer')->restKeysTable;

        parent::__construct($data);
    }
}