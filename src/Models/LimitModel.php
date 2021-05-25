<?php
namespace Daycry\RestServer\Models;

use CodeIgniter\Model;

class LimitModel extends Model
{
    protected $DBGroup = 'api';

    protected $table      = 'limits';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'object';

    protected $useSoftDeletes = true;

    protected $allowedFields = [ 'uri', 'count', 'hour_started', 'api_key' ];

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

    public function setDBGroup( $DBGroup )
    {
        $this->DBGroup = $DBGroup;
    }
}