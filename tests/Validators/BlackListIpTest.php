<?php

namespace Tests\Validators;

use CodeIgniter\Config\Factories;
use CodeIgniter\HTTP\Request;
use CodeIgniter\HTTP\Response;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

use Daycry\RestServer\Database\Seeds\ExampleSeeder;

class BlackListIpTest extends CIUnitTestCase
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
            ['get', 'helloblacklistip', '\Tests\Support\Controllers\HelloBlackListIp::index']
        ];

        $this->withRoutes($routes);

        $this->config = config('RestServer');
    }

    public function testBlackListIpEnable()
    {
        $this->withHeaders([
            'X-API-KEY' => 'wco8go0csckk8cckgw4kk40g4c4s0ckkcscggocg'
        ]);

        $result = $this->withBody(
            json_encode(['test' => 'helloblacklistip'])
        )->call('get', 'helloblacklistip');

        $content = \json_decode($result->getJson());

        $result->assertStatus(401);
        $this->assertObjectHasAttribute("error", $content->messages);
        $this->assertSame("IP denied", $content->messages->error);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
