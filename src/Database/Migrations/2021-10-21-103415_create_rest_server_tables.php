<?php namespace Daycry\RestServer\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRestServerTables extends Migration
{
    public function up()
    {
        $config = $this->_getConfig();

        /*
         * Petitions
         */
        $this->forge->addField([
            'id'                    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'controller'            => ['type' => 'varchar', 'constraint' => 255, 'null' => true, 'default' => null],
            'method'                => ['type' => 'varchar', 'constraint' => 100, 'null' => true, 'default' => null],
            'http'                  => ['type' => 'varchar', 'constraint' => 10, 'null' => true, 'default' => null],
            'auth'                  => ['type' => 'varchar', 'constraint' => 10, 'null' => true, 'default' => null],
            'key'                   => ['type' => 'tinyint', 'constraint' => 1,'null' => true, 'default' => null],
            'log'                   => ['type' => 'tinyint', 'constraint' => 1,'null' => true, 'default' => null],
            'limit'                 => ['type' => 'tinyint', 'constraint' => 1,'null' => true, 'default' => null],
            'time'                  => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'default' => null],
            'level'                 => ['type' => 'tinyint', 'constraint' => 1,'null' => true, 'default' => null],
            'created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'deleted_at'       => ['type' => 'datetime', 'null' => true, 'default' => null]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('deleted_at');
        $this->forge->addUniqueKey(['controller', 'method', 'http']);

        $this->forge->createTable($config->configRestPetitionsTable, true);

        /*
         * Users
         */
        $this->forge->addField([
            'id'                    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'                  => ['type' => 'varchar', 'constraint' => 255, 'null' => false],
            'created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'deleted_at'            => ['type' => 'datetime', 'null' => true, 'default' => null]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('deleted_at');
        $this->forge->addUniqueKey('name');

        $this->forge->createTable($config->restUsersTable, true);

        /*
         * Keys
         */
        $this->forge->addField([
            'id'                       => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'                  => ['type' => 'int', 'unsigned' => true, 'constraint' => 11, 'null' => false],
            $config->restKeyColumn     => ['type' => 'varchar', 'constraint' => $config->restKeyLength, 'null' => false],
            'level'                    => ['type' => 'int', 'constraint' => 2, 'null' => false],
            'ignore_limits'            => ['type' => 'tinyint', 'constraint' => 1, 'null' => false, 'default' => 0],
            'is_private_key'           => ['type' => 'tinyint', 'constraint' => 1, 'null' => false, 'default' => 0],
            'ip_addresses'             => ['type' => 'text', 'null' => null, 'default' => null],
            'created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'deleted_at'            => ['type' => 'datetime', 'null' => true, 'default' => null]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addUniqueKey('key');
        $this->forge->addUniqueKey(['user_id','key']);
        $this->forge->addForeignKey('user_id', $config->restUsersTable, 'id', '', 'CASCADE');

        $this->forge->createTable($config->restKeysTable, true);

        /*
         * Logs
         */
        $this->forge->addField([
            'id'                       => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'uri'                      => ['type' => 'varchar', 'constraint' => 255, 'null' => false],
            'method'                   => ['type' => 'varchar', 'constraint' => 6, 'null' => false],
            'params'                   => ['type' => 'text', 'null' => true, 'default' => null],
            'api_key'                  => ['type' => 'varchar', 'constraint' => $config->restKeyLength, 'null' => true, 'default' => null],
            'ip_address'               => ['type' => 'varchar', 'constraint' => 45, 'null' => false],
            'duration'                 => ['type' => 'float', 'null' => true, 'default' => null],
            'authorized'               => ['type' => 'tinyint', 'constraint' => 1, 'null' => false],
            'response_code'            => ['type' => 'tinyint', 'constraint' => 3, 'null' => true, 'default' => 0],
            'created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'deleted_at'               => ['type' => 'datetime', 'null' => true, 'default' => null]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('api_key');
        $this->forge->addKey('deleted_at');

        $this->forge->createTable($config->configRestLogsTable, true);

        /*
         * Limits
         */
        $this->forge->addField([
            'id'                       => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'uri'                      => ['type' => 'varchar', 'constraint' => 255, 'null' => false],
            'count'                    => ['type' => 'int', 'constraint' => 10, 'null' => false],
            'hour_started'             => ['type' => 'int', 'constraint' => 11, 'null' => false],
            'api_key'                  => ['type' => 'varchar', 'constraint' => $config->restKeyLength, 'null' => true, 'default' => null],
            'created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'deleted_at'               => ['type' => 'datetime', 'null' => true, 'default' => null]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('api_key');
        $this->forge->addKey('deleted_at');

        $this->forge->createTable($config->restLimitsTable, true);

        /*
         * Access
         */
        $this->forge->addField([
            'id'                       => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'api_key'                  => ['type' => 'varchar', 'constraint' => $config->restKeyLength, 'null' => false],
            'all_access'               => ['type' => 'tinyint', 'constraint' => 1, 'null' => false, 'default' => 0],
            'controller'               => ['type' => 'varchar', 'constraint' => 255, 'null' => false],
            'method'                   => ['type' => 'varchar', 'constraint' => 100, 'null' => true, 'default' => null],
            'created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'deleted_at'               => ['type' => 'datetime', 'null' => true, 'default' => null]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('api_key');
        $this->forge->addKey('deleted_at');
        $this->forge->addForeignKey('api_key', $config->restKeysTable, $config->restKeyColumn, '', 'CASCADE');

        $this->forge->createTable($config->restAccessTable, true);
    }

    public function down()
    {
        $config = $this->_getConfig();

		// drop constraints first to prevent errors
        if ($this->db->DBDriver != 'SQLite3') // @phpstan-ignore-line
        {
            $this->forge->dropForeignKey($config->restKeysTable, $config->restKeysTable . '_user_id_foreign');
            $this->forge->dropForeignKey($config->restAccessTable, $config->restAccessTable . '_api_key_foreign');
        }

		$this->forge->dropTable($config->configRestPetitionsTable, true);
        $this->forge->dropTable($config->restUsersTable, true);
        $this->forge->dropTable($config->restKeysTable, true);
        $this->forge->dropTable($config->configRestLogsTable, true);
        $this->forge->dropTable($config->restLimitsTable, true);
        $this->forge->dropTable($config->restAccessTable, true);
    }

    private function _getConfig()
    {
        $config = config( 'RestServer' );

        if( !$config )
        {
            $config = new \Daycry\RestServer\Config\RestServer();
        }

        return $config;
    }
}