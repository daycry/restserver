<?php

namespace Tests\Auth;

use CodeIgniter\Config\Factories;
use CodeIgniter\HTTP\Request;
use CodeIgniter\HTTP\Response;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

use Daycry\RestServer\Database\Seeds\ExampleSeeder;

class WhiteListTest extends CIUnitTestCase
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
            ['get', 'helloauthwhitelist', '\Tests\Support\Controllers\HelloAuthWhiteList::index']
        ];

        $this->withRoutes($routes);

        $this->config = config('RestServer');
    }

    public function testWhiteListSuccess()
    {
        $this->withHeaders([
            'Origin' => 'https://test-cors.local',
            'X-API-KEY' => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123'
        ]);

        $result = $this->withBody(
            json_encode(['test' => 'helloauthwhitelist'])
        )->call('get', 'helloauthwhitelist?format=json');

        $content = \json_decode($result->getJson());

        $result->assertStatus(200);
        $this->assertTrue(isset($content->test));
        $this->assertTrue(isset($content->auth));
        //$this->assertObjectHasAttribute("test", $content);
        //$this->assertObjectHasAttribute("auth", $content);
        $this->AssertSame("helloauthwhitelist", $content->test);
        $this->AssertSame("0.0.0.0", $content->auth);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
