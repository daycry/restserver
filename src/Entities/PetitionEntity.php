<?php

namespace Daycry\RestServer\Entities;

use CodeIgniter\Entity\Entity;

class PetitionEntity extends Entity 
{
    use \Tatter\Relations\Traits\EntityTrait;

    protected $table      = 'ws_request';
    protected $primaryKey = 'id';

    public function __construct(?array $data = null)
    {
        $this->table = config('RestServer')->configRestPetitionsTable;

        parent::__construct($data);
    }
}