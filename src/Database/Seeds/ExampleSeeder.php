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
                'auth'      => null,
                'key'       => 1,
                'limit'     => 100,
                'time'      => 1800,
                'level'     => 10
            ],
            [
                'controller'=> '\Tests\Support\Controllers\Hello',
                'method'    => 'index',
                'http'      => null,
                'auth'      => null,
                'key'       => null,
                'limit'     => 100,
                'time'      => 3600,
                'level'     => 10
            ],
            [
                'controller'=> '\Tests\Support\Controllers\HelloIpAddressLimitNoApi',
                'method'    => 'index',
                'http'      => null,
                'auth'      => null,
                'key'       => 0,
                'limit'     => 1,
                'time'      => 3600,
                'level'     => 10
            ],
            [
                'controller'=> '\Tests\Support\Controllers\HelloBlackListIp',
                'method'    => 'index',
                'http'      => null,
                'auth'      => null,
                'key'       => null,
                'limit'     => 100,
                'time'      => 3600,
                'level'     => 10
            ],
            [
                'controller'=> '\Tests\Support\Controllers\NoHello',
                'method'    => null,
                'http'      => null,
                'auth'      => null,
                'key'       => 1,
                'limit'     => 1,
                'time'      => 3600,
                'level'     => 10
            ],
            [
                'controller'=> '\Tests\Support\Controllers\NoAccess',
                'method'    => 'index',
                'http'      => 'GET',
                'auth'      => null,
                'key'       => 1,
                'limit'     => 1,
                'time'      => 3600,
                'level'     => 10
            ],
            [
                'controller'=> '\Tests\Support\Controllers\HelloAuthBasic',
                'method'    => 'validateParams',
                'http'      => 'GET',
                'auth'      => 'basic',
                'key'       => 1,
                'limit'     => null,
                'time'      => 3600,
                'level'     => 10
            ],
            [
                'controller'=> '\Tests\Support\Controllers\HelloAuthBasic',
                'method'    => 'index',
                'http'      => 'GET',
                'auth'      => 'basic',
                'key'       => 1,
                'limit'     => null,
                'time'      => 3600,
                'level'     => 10
            ],
            [
                'controller'=> '\Tests\Support\Controllers\HelloAuthBasicAjax',
                'method'    => 'index',
                'http'      => 'GET',
                'auth'      => 'basic',
                'key'       => 1,
                'limit'     => null,
                'time'      => 3600,
                'level'     => 10
            ],
            [
                'controller'=> '\Tests\Support\Controllers\HelloAuthBearer',
                'method'    => 'index',
                'http'      => 'GET',
                'auth'      => 'bearer',
                'key'       => 1,
                'limit'     => null,
                'time'      => 3600,
                'level'     => 10
            ],
            [
                'controller'=> '\Tests\Support\Controllers\HelloAuthCustomBearer',
                'method'    => 'index',
                'http'      => 'GET',
                'auth'      => 'bearer',
                'key'       => 1,
                'limit'     => null,
                'time'      => 3600,
                'level'     => 10
            ],
            [
                'controller'=> '\Tests\Support\Controllers\HelloAuthSession',
                'method'    => 'index',
                'http'      => 'GET',
                'auth'      => 'session',
                'key'       => 1,
                'limit'     => null,
                'time'      => 3600,
                'level'     => 10
            ],
            [
                'controller'=> '\Tests\Support\Controllers\HelloAuthDigest',
                'method'    => 'index',
                'http'      => 'GET',
                'auth'      => 'digest',
                'key'       => 1,
                'limit'     => null,
                'time'      => 3600,
                'level'     => 10
            ],
            [
                'controller'=> '\Tests\Support\Controllers\HelloAuthLibrary',
                'method'    => 'index',
                'http'      => 'GET',
                'auth'      => 'basic',
                'key'       => 1,
                'limit'     => null,
                'time'      => 3600,
                'level'     => 10
            ],
            [
                'controller'=> '\Tests\Support\Controllers\HelloAuthLibraryError',
                'method'    => 'index',
                'http'      => 'GET',
                'auth'      => 'basic',
                'key'       => 1,
                'limit'     => null,
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
            ,
            [
                $config->restKeyColumn  => '4568go0csckk8cckgw4kk40g4c4s0ckkcscgg456',
                'level'                 => '1',
                'is_private_key'        => 1,
                'ip_addresses'          => '127.0.0.1,10.1.133.13,10.222.180.0/255.252.0'
            ]
        ];

        // Using Query Builder
        $this->db->table($config->restKeysTable)->insertBatch($key);


        $user = [
            [
                'name'      => 'userSample',
                $config->userKeyColumn    => '1'
            ],
            [
                'name'      => 'userSample2',
                $config->userKeyColumn    => '2'
            ]
        ];

        // Using Query Builder
        $this->db->table($config->restUsersTable)->insertBatch($user);


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
                'api_key'       => '4568go0csckk8cckgw4kk40g4c4s0ckkcscgg456',
                'all_access'    => 1,
                'controller'    => '\Tests\Support\Controllers\Hello',
                'method'        => null
            ],
            [
                'api_key'       => 'wco8go0csckk8cckgw4kk40g4c4s0ckkcscggocg',
                'all_access'    => 1,
                'controller'    => '\Tests\Support\Controllers\HelloBlackListIp',
                'method'        => null
            ],
            [
                'api_key'       => 'wco8go0csckk8cckgw4kk40g4c4s0ckkcscggocg',
                'all_access'    => 0,
                'controller'    => '\Tests\Support\Controllers\NoHello',
                'method'        => 'index'
            ],
            [
                'api_key'       => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
                'all_access'    => 0,
                'controller'    => '\Tests\Support\Controllers\HelloAuthBasic',
                'method'        => 'index'
            ],
            [
                'api_key'       => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
                'all_access'    => 0,
                'controller'    => '\Tests\Support\Controllers\HelloAuthBasic',
                'method'        => 'validateParams'
            ],
            [
                'api_key'       => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
                'all_access'    => 0,
                'controller'    => '\Tests\Support\Controllers\HelloAuthBasicAjax',
                'method'        => 'index'
            ],
            [
                'api_key'       => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
                'all_access'    => 0,
                'controller'    => '\Tests\Support\Controllers\HelloAuthBasic',
                'method'        => 'invalid'
            ],
            [
                'api_key'       => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
                'all_access'    => 0,
                'controller'    => '\Tests\Support\Controllers\HelloAuthBearer',
                'method'        => 'index'
            ],
            [
                'api_key'       => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
                'all_access'    => 0,
                'controller'    => '\Tests\Support\Controllers\HelloAuthCustomBearer',
                'method'        => 'index'
            ],
            [
                'api_key'       => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
                'all_access'    => 0,
                'controller'    => '\Tests\Support\Controllers\HelloAuthSession',
                'method'        => 'index'
            ],
            [
                'api_key'       => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
                'all_access'    => 0,
                'controller'    => '\Tests\Support\Controllers\HelloAuthDigest',
                'method'        => 'index'
            ],
            [
                'api_key'       => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
                'all_access'    => 0,
                'controller'    => '\Tests\Support\Controllers\HelloAuthLibrary',
                'method'        => 'index'
            ],
            [
                'api_key'       => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
                'all_access'    => 0,
                'controller'    => '\Tests\Support\Controllers\HelloAuthLibraryError',
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