<?php

namespace Daycry\RestServer\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ExampleSeeder extends Seeder
{
    public function run()
    {
        $config = $this->_getConfig();

        $petition = [
            'controller'=> '\App\Controllers\Login',
            'method'    => 'doLogin',
            'http'      => 'POST',
            'limit'     => 100,
            'time'      => 15,
            'level'     => 10
        ];

        // Using Query Builder
        $this->db->table($config->configRestPetitionsTable)->insert($petition);

        $key = [
            $config->restKeyColumn  => 'wco8go0csckk8cckgw4kk40g4c4s0ckkcscggocg',
            'level'                 => '10',
            'is_private_key'        => 1,
            'ip_addresses'          => '127.0.0.1,10.1.133.13,10.222.180.0/255.252.0'
        ];

        // Using Query Builder
        $this->db->table($config->restKeysTable)->insert($key);

        $access = [
            'api_key'       => 'wco8go0csckk8cckgw4kk40g4c4s0ckkcscggocg',
            'all_access'    => 0,
            'controller'    => '\App\Controllers\Login',
            'method'        => 'doLogin'
        ];

        // Using Query Builder
        $this->db->table($config->restAccessTable)->insert($access);
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
