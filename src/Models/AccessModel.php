<?php

namespace Daycry\RestServer\Models;

use CodeIgniter\Model;
use CodeIgniter\Validation\ValidationInterface;
use CodeIgniter\Database\ConnectionInterface;
use Config\Database;

class AccessModel extends Model
{
    use \Tatter\Relations\Traits\ModelTrait;

    protected $DBGroup = 'default';

    protected $table      = 'ws_access';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = \Daycry\RestServer\Entities\AccessEntity::class;

    protected $useSoftDeletes = true;

    protected $allowedFields = [ 'key_id', 'all_access', 'controller' ];

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

        $this->table = config('RestServer')->restAccessTable;

        parent::__construct($db, $validation);
    }

    /**
     * @codeCoverageIgnore
     */
    public function addFieldAllowedFields( string $field) :AccessModel
    {
        if( !in_array($field, $this->allowedFields) )
        {
            array_push( $this->allowedFields, $field );
        }

        return $this;
    }
}
