<?php

namespace Daycry\RestServer\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNamespaceTable extends Migration
{
    //protected $DBGroup = 'default';

    public function up()
    {
        $config = $this->_getConfig();

        $this->DBGroup = $config->restDatabaseGroup;

        /*
         * Namespace Table
         */
        $this->forge->addField([
            'id'                    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'url'            => ['type' => 'varchar', 'constraint' => 255, 'null' => false],
            'checked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'deleted_at'       => ['type' => 'datetime', 'null' => true, 'default' => null]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('checked_at');
        $this->forge->addKey('deleted_at');
        $this->forge->addKey('url', false, true);

        $this->forge->createTable($config->restApiTable, true);


        $this->forge->addField([
            'id'                    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'api_id'                    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'controller'            => ['type' => 'varchar', 'constraint' => 255, 'null' => false],
            'methods'                  => ['type' => 'text', 'null' => false],
            'checked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'deleted_at'       => ['type' => 'datetime', 'null' => true, 'default' => null]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('checked_at');
        $this->forge->addKey('deleted_at');
        $this->forge->addKey('controller', false, true);
        $this->forge->addForeignKey('api_id', $config->restApiTable, 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable($config->restNamespaceTable, true);

        /*
         * Request Table
         */ 
        $field = [
            'controller' => [
                'name' => 'namespace_id',
                'type' => 'int',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false
            ]
        ];

        $this->forge->modifyColumn($config->configRestPetitionsTable, $field);

        $this->forge->addColumn($config->configRestPetitionsTable, 'checked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER level;');
        $this->db->query('ALTER TABLE `'.$config->configRestPetitionsTable.'` ADD INDEX `checked_at` (`checked_at`);');
        $this->db->query('ALTER TABLE `'.$config->configRestPetitionsTable.'` ADD CONSTRAINT `'.$config->configRestPetitionsTable.'_namespace_id_foreign` FOREIGN KEY (`namespace_id`) REFERENCES `'.$config->restNamespaceTable.'` (`id`) ON UPDATE RESTRICT ON DELETE CASCADE;');

        /*
         * Access Table
         */
        $this->forge->dropForeignKey($config->restAccessTable, $config->restAccessTable . '_api_key_foreign');
        $this->forge->dropKey($config->restAccessTable, 'api_key');

        $field = [
            'api_key' => [
                'name' => 'key_id',
                'type' => 'int',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false
            ],
        ];

        $this->forge->modifyColumn($config->restAccessTable, $field);

        $field = [
            'controller' => [
                'name' => 'namespace_id',
                'type' => 'int',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false
            ]
        ];

        $this->forge->modifyColumn($config->restAccessTable, $field);

        $this->db->query('ALTER TABLE `'.$config->restAccessTable.'` ADD INDEX `key_id` (`key_id`);');
        $this->db->query('ALTER TABLE `'.$config->restAccessTable.'` ADD CONSTRAINT `'.$config->restAccessTable.'_key_id_foreign` FOREIGN KEY (`key_id`) REFERENCES `'. $config->restKeysTable .'`(`id`);');

        $this->db->query('ALTER TABLE `'.$config->restAccessTable.'` ADD INDEX `namespace_id` (`namespace_id`);');
        $this->db->query('ALTER TABLE `'.$config->restAccessTable.'` ADD CONSTRAINT `'.$config->restAccessTable.'_namespace_id_foreign` FOREIGN KEY (`namespace_id`) REFERENCES `'. $config->restNamespaceTable .'`(`id`);');

        /*
         * Limit Table
         */
        $this->forge->dropKey($config->restLimitsTable, 'api_key');

        $field = [
            'api_key' => [
                'name' => 'key_id',
                'type' => 'int',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true
            ],
        ];

        $this->forge->modifyColumn($config->restLimitsTable, $field);


        $this->db->query('ALTER TABLE `'.$config->restLimitsTable.'` ADD INDEX `key_id` (`key_id`);');
        $this->db->query('ALTER TABLE `'.$config->restLimitsTable.'` ADD CONSTRAINT `'.$config->restLimitsTable.'_key_id_foreign` FOREIGN KEY (`key_id`) REFERENCES `'. $config->restKeysTable .'`(`id`);');

    }

    public function down()
    {
        $config = $this->_getConfig();

        if ($this->db->DBDriver != 'SQLite3') { // @phpstan-ignore-line
            $this->forge->dropForeignKey($config->configRestPetitionsTable, $config->configRestPetitionsTable . '_namespace_id_foreign');
            $this->forge->dropForeignKey($config->restNamespaceTable, $config->restNamespaceTable . '_api_id_foreign');

            $this->forge->dropForeignKey($config->restAccessTable, $config->restAccessTable . '_namespace_id_foreign');
            $this->forge->dropForeignKey($config->restAccessTable, $config->restAccessTable . '_key_id_foreign');
            $this->forge->dropForeignKey($config->restLimitsTable, $config->restLimitsTable . '_key_id_foreign');
        }

        $this->forge->dropTable($config->restNamespaceTable, true);
        $this->forge->dropTable($config->restApiTable, true);
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
