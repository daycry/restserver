<?php

namespace Daycry\RestServer\Models;

use CodeIgniter\Model;
use CodeIgniter\Validation\ValidationInterface;
use CodeIgniter\Database\ConnectionInterface;
use Config\Database;

class LogModel extends Model
{
    use \Tatter\Relations\Traits\ModelTrait;

    protected $DBGroup = 'default';

    protected $table      = 'ws_logs';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = \Daycry\RestServer\Entities\LogEntity::class;

    protected $useSoftDeletes = true;

    protected $allowedFields = [ 'uri', 'method', 'params', 'api_key', 'ip_address', 'duration', 'authorized', 'response_code' ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;

    public function __construct(?ConnectionInterface &$db = null, ?ValidationInterface $validation = null)
    {
        if ($db === null) {
            $db = Database::connect(config('RestServer')->restDatabaseGroup);
            $this->DBGroup = config('RestServer')->restDatabaseGroup;
        }

        $this->table = config('RestServer')->configRestLogsTable;

        parent::__construct($db, $validation);
    }
}
