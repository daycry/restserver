<?php

namespace Daycry\RestServer\Entities;

use CodeIgniter\Entity\Entity;

use Tatter\Relations\Traits\EntityTrait;
use Daycry\RestServer\Traits\Schema;

class AccessEntity extends Entity
{
    use EntityTrait, Schema;

    protected $table      = 'ws_access';
    protected $primaryKey = 'id';

    public function __construct(?array $data = null)
    {
        $this->table = config('RestServer')->restAccessTable;

        parent::__construct($data);

        $this->schema = self::getSchema();
    }
}