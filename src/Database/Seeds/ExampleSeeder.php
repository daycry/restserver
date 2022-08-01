<?php

namespace Daycry\RestServer\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ExampleSeeder extends Seeder
{
    public function run()
    {
        $config = $this->_getConfig();

        $petition = [
            [
                'controller'=> '\App\Controllers\Login',
                'method'    => 'doLogin',
                'http'      => 'POST',
                'key'       => 1,
                'limit'     => 100,
                'time'      => 1800,
                'level'     => 10
            ],
            [
                'controller'=> '\Tests\Support\Controllers\Hello',
                'method'    => 'index',
                'http'      => null,
                'key'       => null,
                'limit'     => 100,
                'time'      => 3600,
                'level'     => 10
            ],
            [
                'controller'=> '\Tests\Support\Controllers\NoHello',
                'method'    => null,
                'http'      => null,
                'key'       => 1,
                'limit'     => 1,
                'time'      => 3600,
                'level'     => 10
            ],
            [
                'controller'=> '\Tests\Support\Controllers\NoAccess',
                'method'    => 'index',
                'http'      => 'GET',
                'key'       => 1,
                'limit'     => 1,
                'time'      => 3600,
                'level'     => 10
            ]
        ];

        // Using Query Builder
        $this->db->table($config->configRestPetitionsTable)->insertBatch($petition);

        $key = [
            [
                $config->restKeyColumn  => 'wco8go0csckk8cckgw4kk40g4c4s0ckkcscggocg',
                'level'                 => '10',
                'is_private_key'        => 1,
                'ip_addresses'          => '0.0.0.0,127.0.0.1,10.1.133.13,10.222.180.0/255.252.0'
            ],
            [
                $config->restKeyColumn  => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
                'level'                 => '10',
                'is_private_key'        => 1,
                'ip_addresses'          => '127.0.0.1,10.1.133.13,10.222.180.0/255.252.0'
            ]
        ];

        // Using Query Builder
        $this->db->table($config->restKeysTable)->insertBatch($key);


        $user = [
            'name'      => 'userSample',
            $config->userKeyColumn    => '1'
        ];

        // Using Query Builder
        $this->db->table($config->restUsersTable)->insert($user);


        $attempt = [
            'ip_address'     => '0.0.0.0',
            'attempts'       => '1000',
            'hour_started'    => 1459363874
        ];

        // Using Query Builder
        $this->db->table($config->restInvalidAttemptsTable)->insert($attempt);

        $access = [
            [
                'api_key'       => 'wco8go0csckk8cckgw4kk40g4c4s0ckkcscggocg',
                'all_access'    => 1,
                'controller'    => '\Tests\Support\Controllers\Hello',
                'method'        => null
            ],
            [
                'api_key'       => 'wco8go0csckk8cckgw4kk40g4c4s0ckkcscggocg',
                'all_access'    => 0,
                'controller'    => '\Tests\Support\Controllers\NoHello',
                'method'        => 'index'
            ]
        ];

        // Using Query Builder
        $this->db->table($config->restAccessTable)->insertBatch($access);
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