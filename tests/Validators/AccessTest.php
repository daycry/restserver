<?php

namespace Tests\Validators;

use CodeIgniter\Config\Factories;
use CodeIgniter\HTTP\Request;
use CodeIgniter\HTTP\Response;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

use Daycry\RestServer\Database\Seeds\ExampleSeeder;

class AccessTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = false;
    protected $refresh     = true;
    protected $seedOnce = false;
    protected $seed = ExampleSeeder::class;
    protected $basePath = HOMEPATH . 'src/Database';
    protected $namespace = 'Daycry\RestServer';

    protected $config;

    protected function setUp(): void
    {
        $this->resetServices();

        parent::setUp();

        $routes = [
            ['get', 'hello', '\Tests\Support\Controllers\Hello::index'],
            ['get', 'nohello', '\Tests\Support\Controllers\NoHello::index'],
            ['get', 'noaccess', '\Tests\Support\Controllers\NoAccess::index']
        ];

        $this->withRoutes($routes);

        $this->config = config('RestServer');
    }

    public function testAccessError()
    {
        $this->withHeaders([
            'Origin' => 'https://test-cors.local',
            'X-API-KEY' => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123'
        ]);

        $result = $this->withBody(
            json_encode(['test' => 'noaccess'])
        )->call('get', 'noaccess');


        $content = \json_decode($result->getJson());

        $result->assertStatus(401);
        $this->assertObjectHasAttribute("error", $content->messages);
        $this->assertMatchesRegularExpression("/does not have access to the requested controller/i", $content->messages->error);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
