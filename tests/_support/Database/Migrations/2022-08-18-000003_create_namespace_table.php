<?php

namespace Tests\Support\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNamespaceTable extends Migration
{
    protected $DBGroup = 'tests';

    public function up()
    {
        $config = $this->_getConfig();

        $this->DBGroup = $config->restDatabaseGroup;

        $field = [
            'controller' => [
                'name' => 'controller',
                'type' => 'varchar',
                'constraint' => 255,
                'null' => false
            ],
        ];

        $this->forge->modifyColumn($config->configRestPetitionsTable, $field);

        /*
         * Namespace
         */
        $this->forge->addField([
            'id'                    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'controller'            => ['type' => 'varchar', 'constraint' => 255, 'null' => false],
            'methods'                  => ['type' => 'varchar', 'constraint' => 45, 'null' => false],
            'created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'deleted_at'       => ['type' => 'datetime', 'null' => true, 'default' => null]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('deleted_at');
        $this->forge->addKey('controller', false, true);

        $this->forge->createTable($config->restNamespaceTable, true);

        $this->db->query('ALTER TABLE `'.$config->configRestPetitionsTable.'` ADD CONSTRAINT `'.$config->configRestPetitionsTable.'_controller_foreign` FOREIGN KEY (`controller`) REFERENCES `'.$config->restNamespaceTable.'` (`controller`) ON UPDATE NO ACTION ON DELETE NO ACTION;');
    }

    public function down()
    {
        $config = $this->_getConfig();

        if ($this->db->DBDriver != 'SQLite3') { // @phpstan-ignore-line
            $this->forge->dropForeignKey($config->configRestPetitionsTable, $config->configRestPetitionsTable . '_controller_foreign');
        }

        $this->forge->dropTable($config->restNamespaceTable, true);
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
