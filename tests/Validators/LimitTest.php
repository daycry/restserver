<?php

namespace Tests\Validators;

use CodeIgniter\Config\Factories;
use CodeIgniter\HTTP\Request;
use CodeIgniter\HTTP\Response;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

use Daycry\RestServer\Database\Seeds\ExampleSeeder;

class LimitTest extends CIUnitTestCase
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
            ['get', 'helloipaddresslimitnoapi', '\Tests\Support\Controllers\HelloIpAddressLimitNoApi::index'],
            ['get', 'hellolimitapikey', '\Tests\Support\Controllers\HelloLimitApiKey::index'],
            ['get', 'hellolimitroutedurl', '\Tests\Support\Controllers\HelloLimitRoutedUrl::index']
        ];

        $this->withRoutes($routes);

        $this->config = config('RestServer');
    }

    public function testLimitSuccess()
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

    public function testLimitIpAddressSuccess()
    {
        $result = $this->withBody(
            json_encode(['test' => 'helloipaddresslimitnoapi'])
        )->call('get', 'helloipaddresslimitnoapi');

        $content = \json_decode($result->getJson());

        $result->assertStatus(200);
        //$this->assertTrue( isset($content->test) );
        //$this->assertTrue( isset($content->auth) );
        //$this->assertObjectHasAttribute("test", $content);
        //$this->assertObjectHasAttribute("auth", $content);
        $this->AssertSame("helloipaddresslimitnoapi", $content->test);
        $this->AssertNull($content->auth);
    }

    public function testLimitApiKeySuccess()
    {
        $this->withHeaders([
            'Origin' => 'https://test-cors.local',
            'X-API-KEY' => 'wco8go0csckk8cckgw4kk40g4c4s0ckkcscggocg'
        ]);

        $result = $this->withBody(
            json_encode(['test' => 'hellolimitapikey'])
        )->call('get', 'hellolimitapikey');

        $result = $this->call('get', 'hellolimitapikey');

        $content = \json_decode($result->getJson());

        $result->assertStatus(200);
        //$this->assertTrue( isset($content->test) );
        //$this->assertTrue( isset($content->auth) );
        //$this->assertObjectHasAttribute("test", $content);
        //$this->assertObjectHasAttribute("auth", $content);
        $this->AssertSame("hellolimitapikey", $content->test);
        $this->AssertNull($content->auth);
    }

    public function testLimitRoutedSuccess()
    {
        $this->withHeaders([
            'Origin' => 'https://test-cors.local',
            'X-API-KEY' => 'wco8go0csckk8cckgw4kk40g4c4s0ckkcscggocg'
        ]);

        $result = $this->withBody(
            json_encode(['test' => 'hellolimitroutedurl'])
        )->call('get', 'hellolimitroutedurl');

        $content = \json_decode($result->getJson());

        $result->assertStatus(200);
        //$this->assertTrue( isset($content->test) );
        //$this->assertTrue( isset($content->auth) );
        //$this->assertObjectHasAttribute("test", $content);
        //$this->assertObjectHasAttribute("auth", $content);
        $this->AssertSame("hellolimitroutedurl", $content->test);
        $this->AssertNull($content->auth);
    }

    public function testLimitIpAddressError()
    {
        $result = $this->withBody(
            json_encode(['test' => 'helloipaddresslimitnoapi'])
        )->call('get', 'helloipaddresslimitnoapi');

        $result2 = $this->call('get', 'helloipaddresslimitnoapi');

        $content = \json_decode($result2->getJson());

        $result->assertStatus(429);
        $this->assertTrue(isset($content->messages->error));
        //$this->assertObjectHasAttribute("error", $content->messages);
        $this->assertSame("This IP Address has reached the time limit for this method", $content->messages->error);
    }

    public function testLimitError()
    {
        $this->withHeaders([
            'Origin' => 'https://test-cors.local',
            'X-API-KEY' => 'wco8go0csckk8cckgw4kk40g4c4s0ckkcscggocg'
        ]);

        $result = $this->withBody(
            json_encode(['test' => 'nohello'])
        )->call('get', 'nohello');

        $result2 = $this->withBody(
            json_encode(['test' => 'nohello'])
        )->call('get', 'nohello');

        $content = \json_decode($result2->getJson());

        $result->assertStatus(429);
        $this->assertTrue(isset($content->messages->error));
        //$this->assertObjectHasAttribute("error", $content->messages);
        $this->assertMatchesRegularExpression("/has reached the time limit for this method/i", $content->messages->error);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
