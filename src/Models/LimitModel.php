<?php

namespace Daycry\RestServer\Models;

use CodeIgniter\Model;
use CodeIgniter\Validation\ValidationInterface;
use CodeIgniter\Database\ConnectionInterface;
use Config\Database;

class LimitModel extends Model
{
    use \Tatter\Relations\Traits\ModelTrait;

    protected $DBGroup = 'default';

    protected $table      = 'ws_limits';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = \Daycry\RestServer\Entities\LimitEntity::class;

    protected $useSoftDeletes = true;

    protected $allowedFields = [ 'uri', 'count', 'hour_started', 'key_id' ];

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

        $this->table = config('RestServer')->restLimitsTable;

        parent::__construct($db, $validation);
    }
}
