<?php

namespace Daycry\RestServer\Entities;

use CodeIgniter\Entity\Entity;

use Tatter\Relations\Traits\EntityTrait;
use Daycry\RestServer\Traits\Schema;

class UserEntity extends Entity
{
    use EntityTrait;
    use Schema;

    protected $table      = 'ws_users';
    protected $primaryKey = 'id';

    public function __construct(?array $data = null)
    {
        $this->table = config('RestServer')->configRestLogsTable;

        parent::__construct($data);

        $this->schema = self::getSchema();
    }
}
