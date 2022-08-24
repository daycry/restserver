<?php

namespace Daycry\RestServer\Libraries\User;

use CodeIgniter\Model;
use CodeIgniter\Validation\ValidationInterface;
use CodeIgniter\Database\ConnectionInterface;
use Config\Database;

abstract class UserAbstract extends Model
{
    use \Tatter\Relations\Traits\ModelTrait;

    protected $DBGroup = 'default';

    protected $table      = 'ws_users';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'object';

    protected $useSoftDeletes = true;

    protected $allowedFields = [ 'name' ];

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

        $this->table = config('RestServer')->restUsersTable;

        $columnKey = config('RestServer')->userKeyColumn;

        if (!in_array($columnKey, $this->allowedFields)) {
            array_push($this->allowedFields, $columnKey);
        }

        parent::__construct($db, $validation);
    }
}
