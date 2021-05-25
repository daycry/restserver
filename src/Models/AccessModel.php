<?php
namespace Daycry\RestServer\Models;

use CodeIgniter\Model;

class AccessModel extends Model
{
    protected $DBGroup = 'api';

    protected $table      = 'access';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'object';

    protected $useSoftDeletes = true;

    protected $allowedFields = [ 'api_key', 'all_access', 'controller' ];

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