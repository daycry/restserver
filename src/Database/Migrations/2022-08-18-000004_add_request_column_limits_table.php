<?php

namespace Daycry\RestServer\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\Forge;
use CodeIgniter\Config\BaseConfig;

class AddColumnRequestInLimitsTable extends Migration
{
    protected $DBGroup = 'default';
    private BaseConfig $config;
    /**
     * Constructor.
     *
     * @param Forge $forge
     */
    public function __construct(?Forge $forge = null)
    {
        $this->config = $this->_getConfig();
        $this->DBGroup = $this->config->restDatabaseGroup;

        parent::__construct($forge);
    }

    public function up()
    {
        $this->DBGroup = $this->config->restDatabaseGroup;

        $cache = \Config\Services::cache();
        $cache->delete('database-schema-' . $this->config->restDatabaseGroup);

        $this->forge->addColumn($this->config->restLimitsTable, [
            'request_id' => [
                'type'       => 'int',
                'constraint' => 11,
                'unsigned' => true,
                'null'       => true,
                'after'      => 'id'
            ],
        ]);

        $this->db->query('ALTER TABLE `'.$this->config->restLimitsTable.'` ADD CONSTRAINT `'.$this->config->restLimitsTable.'_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `'.$this->config->configRestPetitionsTable.'` (`id`) ON UPDATE RESTRICT ON DELETE CASCADE;');
        
        $cache = \Config\Services::cache();
        $cache->delete('database-schema-' . config('RestServer')->restDatabaseGroup);
    }

    public function down()
    {
        if ($this->db->DBDriver != 'SQLite3') { // @phpstan-ignore-line
            $this->forge->dropForeignKey($this->config->restLimitsTable, $this->config->restLimitsTable . '_request_id_foreign');
        }

        $this->forge->dropColumn($this->config->restLimitsTable, 'request_id');

        $cache = \Config\Services::cache();
        $cache->delete('database-schema-' . config('RestServer')->restDatabaseGroup);
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
