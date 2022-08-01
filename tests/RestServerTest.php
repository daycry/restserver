<?php

namespace Daycry\RestServer\Tests;

use CodeIgniter\Config\Factories;
use CodeIgniter\HTTP\Request;
use CodeIgniter\HTTP\Response;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

use Daycry\RestServer\Database\Seeds\ExampleSeeder;

class RestServerTest extends CIUnitTestCase
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
            ['get', 'nohello', '\Tests\Support\Controllers\NoHello::index']
        ];
        
        $this->withRoutes($routes);

        $this->config = config('RestServer');
    }

    public function testWithoutApiKeyRequest()
    {
        $result = $this->withBody(
            json_encode(['test' => 'hello'])
        )->call('get', 'hello');

        $content = \json_decode($result->getJson());

        $result->assertStatus(401);
        $this->assertObjectHasAttribute("error", $content->messages);
        $this->assertStringStartsWith("Invalid API key", $content->messages->error);
    }

    public function testInvalidApiKeyRequest()
    {
        $this->withHeaders([
            'Origin' => 'https://test-cors.local',
            'X-API-KEY' => 'wco8go0csckk8cckgw4kk40g4c4s0ckkcscggo12'
        ]);

        $result = $this->withBody(
            json_encode(['test' => 'hello'])
        )->call('get', 'hello');

        $content = \json_decode($result->getJson());

        $result->assertStatus(401);
        $this->assertObjectHasAttribute("error", $content->messages);
        $this->assertStringStartsWith("Invalid API key", $content->messages->error);
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
    }
}