<?php
namespace Daycry\RestServer\Models;

use CodeIgniter\Model;
use CodeIgniter\Validation\ValidationInterface;
use CodeIgniter\Database\ConnectionInterface;

class KeyModel extends Model
{
    protected $DBGroup = 'default';

    protected $table      = 'keys';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'object';

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
        parent::__construct( $db, $validation );
    }

    public function setTableName( $tableName )
    {
        $this->table = $tableName;
    }

    public function setKeyName( $keyName )
    {
        $this->allowedFields = [ 'user_id', $keyName, 'level', 'ignore_limits', 'is_private_key' ];
    }
}