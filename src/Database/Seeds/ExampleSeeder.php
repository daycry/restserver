<?php

namespace Daycry\RestServer\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ExampleSeeder extends Seeder
{
    public function run()
    {
        $config = $this->_getConfig();

        $namespace = [
            [
                'controller'=> '\App\Controllers\Login',
                'api_id'    => 1,
                'methods'   => json_encode(array('doLogin'))
            ],
            [
                'controller'=> '\Tests\Support\Controllers\Hello',
                'api_id'    => 1,
                'methods'   => json_encode(array('index'))
            ],
            [
                'controller'=> '\Tests\Support\Controllers\HelloAllCors',
                'api_id'    => 1,
                'methods'   => json_encode(array('index'))
            ],
            [
                'controller'=> '\Tests\Support\Controllers\HelloIpAddressLimitNoApi',
                'api_id'    => 1,
                'methods'   => json_encode(array('index'))
            ],
            [
                'controller'=> '\Tests\Support\Controllers\HelloBlackListIp',
                'api_id'    => 1,
                'methods'   => json_encode(array('index'))
            ],
            [
                'controller'=> '\Tests\Support\Controllers\NoHello',
                'api_id'    => 1,
                'methods'   => json_encode(array())
            ],
            [
                'controller'=> '\Tests\Support\Controllers\NoAccess',
                'api_id'    => 1,
                'methods'   => json_encode(array('index'))
            ],
            [
                'controller'=> '\Tests\Support\Controllers\HelloAuthBasic',
                'api_id'    => 1,
                'methods'   => json_encode(array('validateParams'))
            ],
            [
                'controller'=> '\Tests\Support\Controllers\HelloLimitApiKey',
                'api_id'    => 1,
                'methods'   => json_encode(array('index'))
            ],
            [
                'controller'=> '\Tests\Support\Controllers\HelloLimitRoutedUrl',
                'api_id'    => 1,
                'methods'   => json_encode(array('index'))
            ],
            [
                'controller'=> '\Tests\Support\Controllers\HelloAuthBasicAjax',
                'api_id'    => 1,
                'methods'   => json_encode(array('index'))
            ],
            [
                'controller'=> '\Tests\Support\Controllers\HelloAuthBearer',
                'api_id'    => 1,
                'methods'   => json_encode(array('index'))
            ],
            [
                'controller'=> '\Tests\Support\Controllers\HelloAuthCustomBearer',
                'api_id'    => 1,
                'methods'   => json_encode(array('index'))
            ],
            [
                'controller'=> '\Tests\Support\Controllers\HelloAuthSession',
                'api_id'    => 1,
                'methods'   => json_encode(array('index'))
            ],
            [
                'controller'=> '\Tests\Support\Controllers\HelloAuthDigest',
                'api_id'    => 1,
                'methods'   => json_encode(array('index'))
            ],
            [
                'controller'=> '\Tests\Support\Controllers\HelloAuthLibrary',
                'api_id'    => 1,
                'methods'   => json_encode(array('index'))
            ],
            [
                'controller'=> '\Tests\Support\Controllers\HelloAuthLibraryError',
                'api_id'    => 1,
                'methods'   => json_encode(array('index'))
            ]
        ];

        // Using Query Builder
        $this->db->table($config->restNamespaceTable)->insertBatch($namespace);

        $petition = [
            [
                'namespace_id'=> 1,
                'method'    => 'doLogin',
                'http'      => 'POST',
                'auth'      => null,
                'key'       => 1,
                'limit'     => 100,
                'time'      => 1800,
                'level'     => 10
            ],
            [
                'namespace_id'=> 2,
                'method'    => 'index',
                'http'      => null,
                'auth'      => null,
                'key'       => null,
                'limit'     => 100,
                'time'      => 3600,
                'level'     => 10
            ],
            [
                'namespace_id'=> 3,
                'method'    => 'index',
                'http'      => null,
                'auth'      => null,
                'key'       => null,
                'limit'     => 100,
                'time'      => 3600,
                'level'     => 10
            ],
            [
                'namespace_id'=> 4,
                'method'    => 'index',
                'http'      => null,
                'auth'      => null,
                'key'       => 0,
                'limit'     => 1,
                'time'      => 3600,
                'level'     => 10
            ],
            [
                'namespace_id'=> 5,
                'method'    => 'index',
                'http'      => null,
                'auth'      => null,
                'key'       => null,
                'limit'     => 100,
                'time'      => 3600,
                'level'     => 10
            ],
            [
                'namespace_id'=> 6,
                'method'    => null,
                'http'      => null,
                'auth'      => null,
                'key'       => 1,
                'limit'     => 1,
                'time'      => 3600,
                'level'     => 10
            ],
            [
                'namespace_id'=> 7,
                'method'    => 'index',
                'http'      => 'GET',
                'auth'      => null,
                'key'       => 1,
                'limit'     => 1,
                'time'      => 3600,
                'level'     => 10
            ],
            [
                'namespace_id'=> 8,
                'method'    => 'validateParams',
                'http'      => 'GET',
                'auth'      => 'basic',
                'key'       => 1,
                'limit'     => null,
                'time'      => 3600,
                'level'     => 10
            ],
            [
                'namespace_id'=> 8,
                'method'    => 'index',
                'http'      => 'GET',
                'auth'      => 'basic',
                'key'       => 1,
                'limit'     => null,
                'time'      => 3600,
                'level'     => 10
            ],
            [
                'namespace_id'=> 9,
                'method'    => 'index',
                'http'      => 'GET',
                'auth'      => null,
                'key'       => 1,
                'limit'     => 5,
                'time'      => 3600,
                'level'     => 10
            ],
            [
                'namespace_id'=> 10,
                'method'    => 'index',
                'http'      => 'GET',
                'auth'      => null,
                'key'       => 1,
                'limit'     => null,
                'time'      => 3600,
                'level'     => 10
            ],
            [
                'namespace_id'=> 11,
                'method'    => 'index',
                'http'      => 'GET',
                'auth'      => 'basic',
                'key'       => 1,
                'limit'     => null,
                'time'      => 3600,
                'level'     => 10
            ],
            [
                'namespace_id'=> 12,
                'method'    => 'index',
                'http'      => 'GET',
                'auth'      => 'bearer',
                'key'       => 1,
                'limit'     => null,
                'time'      => 3600,
                'level'     => 10
            ],
            [
                'namespace_id'=> 13,
                'method'    => 'index',
                'http'      => 'GET',
                'auth'      => 'bearer',
                'key'       => 1,
                'limit'     => null,
                'time'      => 3600,
                'level'     => 10
            ],
            [
                'namespace_id'=> 14,
                'method'    => 'index',
                'http'      => 'GET',
                'auth'      => 'session',
                'key'       => 1,
                'limit'     => null,
                'time'      => 3600,
                'level'     => 10
            ],
            [
                'namespace_id'=> 15,
                'method'    => 'index',
                'http'      => 'GET',
                'auth'      => 'digest',
                'key'       => 1,
                'limit'     => null,
                'time'      => 3600,
                'level'     => 10
            ],
            [
                'namespace_id'=> 16,
                'method'    => 'index',
                'http'      => 'GET',
                'auth'      => 'basic',
                'key'       => 1,
                'limit'     => null,
                'time'      => 3600,
                'level'     => 10
            ],
            [
                'namespace_id'=> 17,
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
                'level'                 => 10,
                'ignore_limits'         => 0,
                'is_private_key'        => 1,
                'ip_addresses'          => '0.0.0.0,127.0.0.1,10.1.133.13,10.222.180.0/255.252.0'
            ],
            [
                $config->restKeyColumn  => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
                'level'                 => '10',
                'ignore_limits'         => 1,
                'is_private_key'        => 1,
                'ip_addresses'          => '127.0.0.1,10.1.133.13,10.222.180.0/255.252.0'
            ]
            ,
            [
                $config->restKeyColumn  => '4568go0csckk8cckgw4kk40g4c4s0ckkcscgg456',
                'level'                 => '1',
                'ignore_limits'         => 0,
                'is_private_key'        => 1,
                'ip_addresses'          => '127.0.0.1,10.1.133.13,10.222.180.0/255.252.0'
            ]
        ];

        // Using Query Builder
        $this->db->table($config->restKeysTable)->insertBatch($key);

        $limits = [
            [
                'uri'  => 'api-key:wco8go0csckk8cckgw4kk40g4c4s0ckkcscggocg',
                'count'                 => '10',
                'hour_started'        => 1459537345,
                'key_id'          => 1
            ]
        ];

        // Using Query Builder
        $this->db->table($config->restLimitsTable)->insertBatch($limits);

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
                'key_id'       => 1,
                'all_access'    => 1,
                'namespace_id'    => 2,
                'method'        => null
            ],
            [
                'key_id'       => 3,
                'all_access'    => 1,
                'namespace_id'    => 2,
                'method'        => null
            ],
            [
                'key_id'       => 1,
                'all_access'    => 1,
                'namespace_id'    => 5,
                'method'        => null
            ],
            [
                'key_id'       => 1,
                'all_access'    => 1,
                'namespace_id'    => 9,
                'method'        => null
            ],
            [
                'key_id'       => 1,
                'all_access'    => 1,
                'namespace_id'    => 10,
                'method'        => null
            ],
            [
                'key_id'       => 1,
                'all_access'    => 0,
                'namespace_id'    => 6,
                'method'        => 'index'
            ],
            [
                'key_id'       => 2,
                'all_access'    => 0,
                'namespace_id'    => 8,
                'method'        => 'index'
            ],
            [
                'key_id'       => 2,
                'all_access'    => 0,
                'namespace_id'    => 8,
                'method'        => 'validateParams'
            ],
            [
                'key_id'       => 2,
                'all_access'    => 0,
                'namespace_id'    => 11,
                'method'        => 'index'
            ],
            [
                'key_id'       => 2,
                'all_access'    => 0,
                'namespace_id'    => 8,
                'method'        => 'invalid'
            ],
            [
                'key_id'       => 2,
                'all_access'    => 0,
                'namespace_id'    => 12,
                'method'        => 'index'
            ],
            [
                'key_id'       => 2,
                'all_access'    => 0,
                'namespace_id'    => 13,
                'method'        => 'index'
            ],
            [
                'key_id'       => 2,
                'all_access'    => 0,
                'namespace_id'    => 14,
                'method'        => 'index'
            ],
            [
                'key_id'       => 2,
                'all_access'    => 0,
                'namespace_id'    => 15,
                'method'        => 'index'
            ],
            [
                'key_id'       => 2,
                'all_access'    => 0,
                'namespace_id'    => 16,
                'method'        => 'index'
            ],
            [
                'key_id'       => 2,
                'all_access'    => 0,
                'namespace_id'    => 17,
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
