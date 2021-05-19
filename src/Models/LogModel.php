<?php
namespace Daycry\RestServer\Models;

use CodeIgniter\Model;

class LogModel extends Model
{
    protected $DBGroup = 'api';

    protected $table      = 'logs';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'object';

    protected $useSoftDeletes = true;

    protected $allowedFields = [ 'uri', 'method', 'params', 'api_key', 'ip_address', 'duration', 'authorized', 'response_code' ];

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