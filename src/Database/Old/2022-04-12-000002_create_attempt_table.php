<?php

namespace Daycry\RestServer\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\Forge;
use CodeIgniter\Database\RawSql;

class CreateAttemptTable extends Migration
{
    protected $DBGroup = 'default';

    /**
     * Constructor.
     *
     * @param Forge $forge
     */
    public function __construct(?Forge $forge = null)
    {
        $config = $this->_getConfig();
        $this->DBGroup = $config->restDatabaseGroup;

        parent::__construct($forge);
    }

    public function up()
    {
        $config = $this->_getConfig();

        /*
         * Petitions
         */
        $this->forge->addField([
            'id'                    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'ip_address'            => ['type' => 'varchar', 'constraint' => 45, 'null' => false],
            'attempts'                  => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => false, 'default' => 0],
            'hour_started'             => ['type' => 'int', 'constraint' => 11, 'null' => false],
            'created_at'            => ['type' => 'datetime', 'null' => true, 'default' => new RawSql('CURRENT_TIMESTAMP')],
            'updated_at'            => ['type' => 'datetime', 'null' => true, 'default' => null ],
            //'updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'deleted_at'       => ['type' => 'datetime', 'null' => true, 'default' => null]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('deleted_at');
        $this->forge->addUniqueKey('ip_address');

        $this->forge->createTable($config->restInvalidAttemptsTable, true);
    }

    public function down()
    {
        $config = $this->_getConfig();

        $this->forge->dropTable($config->restInvalidAttemptsTable, true);
    }

    private function _getConfig()
    {
        $config = config('RestServer');

        if (!$config) {
            $config = new \Daycry\RestServer\Config\RestServer();
        }

        return $config;
    }
}
