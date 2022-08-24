<?php

namespace Daycry\RestServer\Entities;

use CodeIgniter\Entity\Entity;

class AttemptEntity extends Entity
{
    use \Tatter\Relations\Traits\EntityTrait;

    protected $table      = 'ws_attempts';
    protected $primaryKey = 'id';

    public function __construct(?array $data = null)
    {
        $this->table = config('RestServer')->restInvalidAttemptsTable;

        parent::__construct($data);
    }
}