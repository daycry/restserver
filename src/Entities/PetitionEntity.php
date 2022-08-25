<?php

namespace Daycry\RestServer\Entities;

use CodeIgniter\Entity\Entity;

use Tatter\Relations\Traits\EntityTrait;
use Daycry\RestServer\Traits\Schema;

class PetitionEntity extends Entity 
{
    use EntityTrait, Schema;

    protected $table      = 'ws_request';
    protected $primaryKey = 'id';

    public function __construct(?array $data = null)
    {
        $this->table = config('RestServer')->configRestPetitionsTable;

        parent::__construct($data);

        $this->schema = self::getSchema();
    }
}