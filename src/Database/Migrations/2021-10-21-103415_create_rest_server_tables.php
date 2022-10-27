<?php

namespace Daycry\RestServer\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\Forge;
use CodeIgniter\Database\RawSql;

use Daycry\RestServer\Config\RestServer;

class CreateRestServerTables extends Migration
{
    protected $DBGroup = 'default';

    protected \Daycry\RestServer\Config\RestServer $config;
    /**
     * Constructor.
     *
     * @param Forge $forge
     */
    public function __construct(?Forge $forge = null)
    {
        $this->config = new RestServer();
        $this->DBGroup = $this->config->restDatabaseGroup;

        parent::__construct($forge);
    }

    public function up()
    {
        /*
         * Apis Table
         */
        $this->forge->addField([
            'id'                    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'url'            => ['type' => 'varchar', 'constraint' => 255, 'null' => false],
            'checked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'created_at'            => ['type' => 'datetime', 'null' => true, 'default' => new RawSql('CURRENT_TIMESTAMP')],
            'updated_at'            => ['type' => 'datetime', 'null' => true, 'default' => null ],
            //'updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'deleted_at'       => ['type' => 'datetime', 'null' => true, 'default' => null]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('checked_at');
        $this->forge->addKey('deleted_at');
        $this->forge->addKey('url', false, true);

        $this->forge->createTable($this->config->restApiTable, true);

        /*
         * Namespaces Table
         */
        $this->forge->addField([
            'id'                    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'api_id'                    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'controller'            => ['type' => 'varchar', 'constraint' => 255, 'null' => false],
            'methods'                  => ['type' => 'text', 'null' => false],
            'checked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'created_at'            => ['type' => 'datetime', 'null' => true, 'default' => new RawSql('CURRENT_TIMESTAMP')],
            'updated_at'            => ['type' => 'datetime', 'null' => true, 'default' => null ],
            //'updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'deleted_at'       => ['type' => 'datetime', 'null' => true, 'default' => null]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('checked_at');
        $this->forge->addKey('deleted_at');
        $this->forge->addKey('controller', false, true);
        $this->forge->addForeignKey('api_id', $this->config->restApiTable, 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable($this->config->restNamespaceTable, true);


        /*
         * Requests Table
         */
        $this->forge->addField([
            'id'                    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'namespace_id'          => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'method'                => ['type' => 'varchar', 'constraint' => 100, 'null' => true, 'default' => null],
            'http'                  => ['type' => 'varchar', 'constraint' => 10, 'null' => true, 'default' => null],
            'auth'                  => ['type' => 'varchar', 'constraint' => 10, 'null' => true, 'default' => null],
            'key'                   => ['type' => 'tinyint', 'constraint' => 1,'null' => true, 'default' => null],
            'log'                   => ['type' => 'tinyint', 'constraint' => 1,'null' => true, 'default' => null],
            'limit'                 => ['type' => 'tinyint', 'constraint' => 1,'null' => true, 'default' => null],
            'time'                  => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'default' => null],
            'level'                 => ['type' => 'tinyint', 'constraint' => 1,'null' => true, 'default' => null],
            'checked_at'            => ['type' => 'datetime', 'null' => true, 'default' => new RawSql('CURRENT_TIMESTAMP')],
            'created_at'            => ['type' => 'datetime', 'null' => true, 'default' => new RawSql('CURRENT_TIMESTAMP')],
            'updated_at'            => ['type' => 'datetime', 'null' => true, 'default' => null ],
            //'updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'deleted_at'       => ['type' => 'datetime', 'null' => true, 'default' => null]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('deleted_at');
        $this->forge->addUniqueKey(['namespace_id', 'method', 'http']);
        $this->forge->addForeignKey('namespace_id', $this->config->restNamespaceTable, 'id', 'RESTRICT', 'CASCADE');

        $this->forge->createTable($this->config->configRestPetitionsTable, true);


        /*
         * Keys Table
         */
        $this->forge->addField([
            'id'                       => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            $this->config->restKeyColumn     => ['type' => 'varchar', 'constraint' => $this->config->restKeyLength, 'null' => false],
            'level'                    => ['type' => 'int', 'constraint' => 2, 'null' => false],
            'ignore_limits'            => ['type' => 'tinyint', 'constraint' => 1, 'null' => false, 'default' => 0],
            'is_private_key'           => ['type' => 'tinyint', 'constraint' => 1, 'null' => false, 'default' => 0],
            'ip_addresses'             => ['type' => 'text', 'null' => null, 'default' => null],
            'created_at'            => ['type' => 'datetime', 'null' => true, 'default' => new RawSql('CURRENT_TIMESTAMP')],
            'updated_at'            => ['type' => 'datetime', 'null' => true, 'default' => null ],
            //'updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'deleted_at'       => ['type' => 'datetime', 'null' => true, 'default' => null]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('key');

        $this->forge->createTable($this->config->restKeysTable, true);

        /*
         * Users
         */
        $this->forge->addField([
            'id'                    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'                  => ['type' => 'varchar', 'constraint' => 255, 'null' => false],
            $this->config->userKeyColumn  => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'            => ['type' => 'datetime', 'null' => true, 'default' => new RawSql('CURRENT_TIMESTAMP')],
            'updated_at'            => ['type' => 'datetime', 'null' => true, 'default' => null ],
            //'updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'deleted_at'       => ['type' => 'datetime', 'null' => true, 'default' => null]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('deleted_at');
        $this->forge->addKey($this->config->userKeyColumn);
        $this->forge->addUniqueKey('name');
        $this->forge->addForeignKey($this->config->userKeyColumn, $this->config->restKeysTable, 'id', '', 'CASCADE');

        $this->forge->createTable($this->config->restUsersTable, true);

        /*
         * Logs
         */
        $this->forge->addField([
            'id'                       => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'uri'                      => ['type' => 'varchar', 'constraint' => 255, 'null' => false],
            'method'                   => ['type' => 'varchar', 'constraint' => 6, 'null' => false],
            'params'                   => ['type' => 'text', 'null' => true, 'default' => null],
            'api_key'                  => ['type' => 'varchar', 'constraint' => $this->config->restKeyLength, 'null' => true, 'default' => null],
            'ip_address'               => ['type' => 'varchar', 'constraint' => 45, 'null' => false],
            'duration'                 => ['type' => 'float', 'null' => true, 'default' => null],
            'authorized'               => ['type' => 'tinyint', 'constraint' => 1, 'null' => false],
            'response_code'            => ['type' => 'int', 'constraint' => 11, 'null' => true, 'default' => 0],
            'created_at'            => ['type' => 'datetime', 'null' => true, 'default' => new RawSql('CURRENT_TIMESTAMP')],
            'updated_at'            => ['type' => 'datetime', 'null' => true, 'default' => null ],
            //'updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'deleted_at'       => ['type' => 'datetime', 'null' => true, 'default' => null]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('api_key');
        $this->forge->addKey('deleted_at');

        $this->forge->createTable($this->config->configRestLogsTable, true);

        /*
         * Limits Table
         */
        $this->forge->addField([
            'id'                       => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'request_id'               => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'uri'                      => ['type' => 'varchar', 'constraint' => 255, 'null' => false],
            'count'                    => ['type' => 'int', 'constraint' => 10, 'null' => false],
            'hour_started'             => ['type' => 'int', 'constraint' => 11, 'null' => false],
            'key_id'                  => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'            => ['type' => 'datetime', 'null' => true, 'default' => new RawSql('CURRENT_TIMESTAMP')],
            'updated_at'            => ['type' => 'datetime', 'null' => true, 'default' => null ],
            //'updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'deleted_at'       => ['type' => 'datetime', 'null' => true, 'default' => null]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('api_key');
        $this->forge->addKey('deleted_at');
        $this->forge->addForeignKey('request_id', $this->config->configRestPetitionsTable, 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('key_id', $this->config->restKeysTable, 'id', 'RESTRICT', 'CASCADE');

        $this->forge->createTable($this->config->restLimitsTable, true);

        /*
         * Accesses Table
         */
        $this->forge->addField([
            'id'                       => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'key_id'                  => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'all_access'               => ['type' => 'tinyint', 'constraint' => 1, 'null' => false, 'default' => 0],
            'namespace_id'          => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'method'                   => ['type' => 'varchar', 'constraint' => 100, 'null' => true, 'default' => null],
            'created_at'            => ['type' => 'datetime', 'null' => true, 'default' => new RawSql('CURRENT_TIMESTAMP')],
            'updated_at'            => ['type' => 'datetime', 'null' => true, 'default' => null ],
            //'updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'deleted_at'       => ['type' => 'datetime', 'null' => true, 'default' => null]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('api_key');
        $this->forge->addKey('deleted_at');
        $this->forge->addForeignKey('key_id', $this->config->restKeysTable, 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('namespace_id', $this->config->restNamespaceTable, 'id', 'RESTRICT', 'CASCADE');

        $this->forge->createTable($this->config->restAccessTable, true);

        /*
         * Attempts Table
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

        $this->forge->createTable($this->config->restInvalidAttemptsTable, true);
    }

    public function down()
    {
        $this->db->disableForeignKeyChecks();

        // drop constraints first to prevent errors
        /*if ($this->db->DBDriver != 'SQLite3') { // @phpstan-ignore-line
            $this->forge->dropForeignKey($config->restUsersTable, $config->restUsersTable . '_key_id_foreign');
            
            $this->forge->dropForeignKey($config->configRestPetitionsTable, $config->configRestPetitionsTable . '_namespace_id_foreign');

            $this->forge->dropForeignKey($config->restNamespaceTable, $config->restNamespaceTable . '_api_id_foreign');

            $this->forge->dropForeignKey($config->restAccessTable, $config->restAccessTable . '_namespace_id_foreign');
            $this->forge->dropForeignKey($config->restAccessTable, $config->restAccessTable . '_key_id_foreign');
            
            $this->forge->dropForeignKey($config->restLimitsTable, $config->restLimitsTable . '_key_id_foreign');
            $this->forge->dropForeignKey($config->restLimitsTable, $this->config->restLimitsTable . '_request_id_foreign');
        }*/

        $this->forge->dropTable($this->config->restNamespaceTable, true, true);
        $this->forge->dropTable($this->config->restApiTable, true, true);
        $this->forge->dropTable($this->config->configRestPetitionsTable, true, true);
        $this->forge->dropTable($this->config->restUsersTable, true, true);
        $this->forge->dropTable($this->config->restKeysTable, true, true);
        $this->forge->dropTable($this->config->configRestLogsTable, true, true);
        $this->forge->dropTable($this->config->restLimitsTable, true, true);
        $this->forge->dropTable($this->config->restAccessTable, true, true);
        $this->forge->dropTable($this->config->restInvalidAttemptsTable, true, true);

        $this->db->enableForeignKeyChecks();
    }
}
