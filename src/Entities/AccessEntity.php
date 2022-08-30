<?php

namespace Daycry\RestServer\Entities;

use CodeIgniter\Entity\Entity;

use Tatter\Relations\Traits\EntityTrait;
use Daycry\RestServer\Traits\Schema;

class AccessEntity extends Entity
{
    use EntityTrait;
    use Schema;

    protected $DBGroup = 'default';
    protected $table      = 'ws_access';
    protected $primaryKey = 'id';

    public function __construct(?array $data = null)
    {
        $this->DBGroup = config('RestServer')->restDatabaseGroup;
        $this->table = config('RestServer')->restAccessTable;

        parent::__construct($data);

        $this->schema = self::getSchema();
    }
}
