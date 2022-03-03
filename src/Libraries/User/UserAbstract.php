<?php
namespace Daycry\RestServer\Libraries\User;

use CodeIgniter\Model;
use CodeIgniter\Validation\ValidationInterface;
use CodeIgniter\Database\ConnectionInterface;
use Config\Database;

abstract class UserAbstract extends Model
{
    protected $DBGroup = 'default';

    protected $table      = 'users';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'object';

    protected $useSoftDeletes = true;

    protected $allowedFields = [ 'name', 'key_id' ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;

    public function __construct(?ConnectionInterface &$db = null, ?ValidationInterface $validation = null)
    {
        if( $db === null ) {
            $db = Database::connect( config('RestServer')->restDatabaseGroup );
        }
        parent::__construct( $db, $validation );
    }

    public function setTableName( $tableName, $columnKey )
    {
        $this->table = $tableName;

        if( !in_array( $columnKey, $this->allowedFields) )
        {
            array_push( $this->allowedFields, $columnKey );
        }
        //$this->allowedFields = [ 'name', $columnKey ];
    }
}