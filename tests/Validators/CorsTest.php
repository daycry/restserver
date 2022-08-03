<?php

namespace Daycry\RestServer\Tests\Validators;

use CodeIgniter\Config\Factories;
use CodeIgniter\HTTP\Request;
use CodeIgniter\HTTP\Response;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

use Daycry\RestServer\Database\Seeds\ExampleSeeder;

class CorsTest extends CIUnitTestCase
{
    use DatabaseTestTrait, FeatureTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = false;
    protected $refresh     = true;
    protected $seedOnce = false;
    protected $seed = ExampleSeeder::class;

    protected $config;

    protected function setUp(): void
    {
        $this->resetServices();

        parent::setUp();

        $routes = [
            ['get', 'hello', '\Tests\Support\Controllers\Hello::index'],
            ['get', 'helloallcors', '\Tests\Support\Controllers\HelloAllCors::index'],
            ['options', 'helloallcors', '\Tests\Support\Controllers\HelloAllCors::index'],
            ['get', 'nohello', '\Tests\Support\Controllers\NoHello::index']
        ];
        
        $this->withRoutes($routes);

        $this->config = config('RestServer');
    }

    public function testCorsError()
    {
        $this->withHeaders([
            'Origin' => 'https://test-failed.local',
            'X-API-KEY' => 'wco8go0csckk8cckgw4kk40g4c4s0ckkcscggocg'
        ]);

        $result = $this->withBody(
            json_encode(['test' => 'hello'])
        )->call('get', 'hello');

        $result->assertHeaderMissing('Access-Control-Allow-Origin');
        $result->assertHeader('Access-Control-Allow-Credentials');
    }

    public function testCorsAllCors()
    {
        $this->withHeaders([
            'Origin' => 'https://test-failed.local',
            'X-API-KEY' => 'wco8go0csckk8cckgw4kk40g4c4s0ckkcscggocg'
        ]);

        $result = $this->withBody(
            json_encode(['test' => 'helloallcors'])
        )->call('get', 'helloallcors');

        $result->assertHeader('Access-Control-Allow-Origin');
        $result->assertHeader('Access-Control-Allow-Headers', implode(", ", $this->config->allowedCorsHeaders));
        $result->assertHeader('Access-Control-Allow-Credentials');
    }

    public function testCorsSuccess()
    {
        $this->withHeaders([
            'Origin' => 'https://test-cors.local',
            'X-API-KEY' => 'wco8go0csckk8cckgw4kk40g4c4s0ckkcscggocg'
        ]);

        $result = $this->withBody(
            json_encode(['test' => 'hello'])
        )->call('get', 'hello');

        $result->assertHeader('Access-Control-Allow-Origin');
        $result->assertHeader('Access-Control-Allow-Headers', implode(", ", $this->config->allowedCorsHeaders));
        $result->assertHeader('Access-Control-Allow-Credentials');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}