<?php
namespace Daycry\RestServer\Models;

use CodeIgniter\Model;

class PetitionModel extends Model
{
    protected $DBGroup = 'api';

    protected $table      = 'petitions';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'object';

    protected $useSoftDeletes = true;

    protected $allowedFields = [ 'controller', 'method', 'auth', 'http', 'log', 'limit', 'level' ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;

    public function setTableName( $tableName )
    {
        $this->table = $tableName;
    }
}