<?php

namespace Daycry\RestServer\Models;

use CodeIgniter\Model;
use CodeIgniter\Validation\ValidationInterface;
use CodeIgniter\Database\ConnectionInterface;
use Config\Database;

class KeyModel extends Model
{
    use \Tatter\Relations\Traits\ModelTrait;

    protected $DBGroup = 'default';

    protected $table      = 'ws_keys';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = \Daycry\RestServer\Entities\KeyEntity::class;

    protected $useSoftDeletes = true;

    protected $allowedFields = [];

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

        $this->table = config('RestServer')->restKeysTable;
        $this->allowedFields = [ config('RestServer')->restKeyColumn, 'level', 'ignore_limits', 'is_private_key', 'ip_addresses' ];

        parent::__construct($db, $validation);
    }
}
