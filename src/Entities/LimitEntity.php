<?php

namespace Daycry\RestServer\Entities;

use CodeIgniter\Entity\Entity;

use Tatter\Relations\Traits\EntityTrait;
use Daycry\RestServer\Traits\Schema;

class LimitEntity extends Entity
{
    use EntityTrait;
    use Schema;

    protected $DBGroup = 'default';
    protected $table      = 'ws_limits';
    protected $primaryKey = 'id';

    public function __construct(?array $data = null)
    {
        $this->DBGroup = config('RestServer')->restDatabaseGroup;
        $this->table = config('RestServer')->restLimitsTable;

        parent::__construct($data);

        $this->schema = self::getSchema();
    }
}
