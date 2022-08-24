<?php

namespace Daycry\RestServer\Entities;

use CodeIgniter\Entity\Entity;

class LogEntity extends Entity
{
    use \Tatter\Relations\Traits\EntityTrait;

    protected $table      = 'ws_logs';
    protected $primaryKey = 'id';

    public function __construct(?array $data = null)
    {
        $this->table = config('RestServer')->configRestLogsTable;

        parent::__construct($data);
    }
}