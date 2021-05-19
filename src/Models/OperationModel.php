<?php
namespace Daycry\RestServer\Models;

use CodeIgniter\Model;

class OperationModel extends Model
{
    protected $DBGroup = 'api';

    protected $table      = 'operations';

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
}