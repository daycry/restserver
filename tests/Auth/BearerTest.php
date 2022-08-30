<?php

namespace Tests\Auth;

use CodeIgniter\Config\Factories;
use CodeIgniter\HTTP\Request;
use CodeIgniter\HTTP\Response;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

use Daycry\RestServer\Database\Seeds\ExampleSeeder;

class BearerTest extends CIUnitTestCase
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
            ['get', 'helloauthbearer', '\Tests\Support\Controllers\HelloAuthBearer::index'],
            ['get', 'helloauthcustombearer', '\Tests\Support\Controllers\HelloAuthCustomBearer::index']
        ];

        $this->withRoutes($routes);

        $this->config = config('RestServer');
    }

    public function testBearerError()
    {
        $bearer = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsInR5cGUiOiJCZWFyZXIifQ.eyJkYXRhIjoiYWRtaW4xIiwiaXNzIjoiaHR0cDovL2V4YW1wbGUubG9jYWwiLCJhdWQiOiJodHRwOi8vZXhhbXBsZS5sb2NhbCIsImp0aSI6IjRmMWcyM2ExMmFhIiwiaWF0IjoxNjU5NDMzNzM1LjcwNDYyMSwibmJmIjoxNjU5NDMzNzM1LjcwNDYyMSwiZXhwIjoxNjU5NTIwMTM1LjcwNDYyMX0.QUH-wuRJPCi4ha-_JjCUoPepulslqR11l6VbSNR_IcE';

        $this->withHeaders([
            'Origin' => 'https://test-cors.local',
            'X-API-KEY' => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
            'Authorization' => 'Bearer ' . $bearer
        ]);

        $result = $this->withBody(
            json_encode(['test' => 'helloauthbearer'])
        )->call('get', 'helloauthbearer');


        $content = \json_decode($result->getJson());

        $result->assertStatus(401);
        $this->assertObjectHasAttribute("error", $content->messages);
        $this->assertSame("Invalid credentials", $content->messages->error);
    }

    public function testBearerErrorNoBearer()
    {
        $bearer = '';

        $this->withHeaders([
            'Origin' => 'https://test-cors.local',
            'X-API-KEY' => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
            'Authorization' => 'Bearer ' . $bearer
        ]);

        $result = $this->withBody(
            json_encode(['test' => 'helloauthbearer'])
        )->call('get', 'helloauthbearer');


        $content = \json_decode($result->getJson());

        $result->assertStatus(401);
        $this->assertObjectHasAttribute("error", $content->messages);
        $this->AssertSame("Invalid credentials", $content->messages->error);
    }

    public function testBearerSuccess()
    {
        $jwtLibrary = new \Daycry\JWT\JWT();
        $bearer = $jwtLibrary->encode('admin');

        $this->withHeaders([
            'Origin' => 'https://test-cors.local',
            'X-API-KEY' => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
            'Authorization' => 'Bearer ' . $bearer
        ]);

        $result = $this->withBody(
            json_encode(['test' => 'helloauthbearer'])
        )->call('get', 'helloauthbearer');

        $content = \json_decode($result->getJson());

        $result->assertStatus(200);
        $this->assertObjectHasAttribute("test", $content);
        $this->assertObjectHasAttribute("auth", $content);
        $this->assertObjectHasAttribute("key", $content);
        $this->assertObjectHasAttribute("ws_users", $content);
        $this->assertIsArray($content->ws_users);
        $this->assertObjectHasAttribute('name', $content->ws_users[0]);
        $this->AssertSame("helloauthbearer", $content->test);
        $this->AssertSame("userSample2", $content->ws_users[0]->name);
        $this->AssertSame("admin", $content->auth);
        $this->AssertSame("1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123", $content->key);
    }

    public function testBearerWrongError()
    {
        $config = new \Daycry\JWT\Config\JWT();
        $config->identifier = '12345';
        $jwtLibrary = new \Daycry\JWT\JWT($config);
        $jwtLibrary->setParamData('data');
        $bearer = $jwtLibrary->encode('admin', 'uid');

        $this->withHeaders([
            'Origin' => 'https://test-cors.local',
            'X-API-KEY' => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
            'Authorization' => 'Bearer ' . $bearer
        ]);

        $result = $this->withBody(
            json_encode(['test' => 'helloauthbearer'])
        )->call('get', 'helloauthbearer');

        $content = \json_decode($result->getJson());

        $result->assertStatus(401);
        $this->assertObjectHasAttribute("error", $content->messages);
        $this->assertSame("Invalid credentials", $content->messages->error);
    }

    public function testBearerSetParamSuccess()
    {
        $jwtLibrary = new \Daycry\JWT\JWT();
        $jwtLibrary->setParamData('data');
        $bearer = $jwtLibrary->encode('admin', 'uid');

        $this->withHeaders([
            'Origin' => 'https://test-cors.local',
            'X-API-KEY' => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
            'Authorization' => 'Bearer ' . $bearer
        ]);

        $result = $this->withBody(
            json_encode(['test' => 'helloauthbearer'])
        )->call('get', 'helloauthbearer');

        $content = \json_decode($result->getJson());

        $result->assertStatus(200);
        $this->assertObjectHasAttribute("test", $content);
        $this->assertObjectHasAttribute("auth", $content);
        $this->assertObjectHasAttribute("key", $content);
        $this->assertObjectHasAttribute("ws_users", $content);
        $this->assertIsArray($content->ws_users);
        $this->assertObjectHasAttribute('name', $content->ws_users[0]);
        $this->AssertSame("helloauthbearer", $content->test);
        $this->AssertSame("userSample2", $content->ws_users[0]->name);
        $this->AssertSame("admin", $content->auth);
        $this->AssertSame("1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123", $content->key);
    }

    public function testBearerSplitDataSuccess()
    {
        $jwtLibrary = new \Daycry\JWT\JWT();
        $jwtLibrary->setSplitData(true);
        $data = array('username' => 'admin', 'split' => true);
        $bearer = $jwtLibrary->encode($data);

        $this->withHeaders([
            'Origin' => 'https://test-cors.local',
            'X-API-KEY' => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
            'Authorization' => 'Bearer ' . $bearer
        ]);

        $result = $this->withBody(
            json_encode(['test' => 'helloauthcustombearer'])
        )->call('get', 'helloauthcustombearer');

        $content = \json_decode($result->getJson());

        $result->assertStatus(200);
        $this->assertObjectHasAttribute("test", $content);
        $this->assertObjectHasAttribute("auth", $content);
        $this->assertObjectHasAttribute("key", $content);
        $this->assertObjectHasAttribute("ws_users", $content);
        $this->assertIsArray($content->ws_users);
        $this->assertObjectHasAttribute('name', $content->ws_users[0]);
        $this->AssertSame("helloauthcustombearer", $content->test);
        $this->AssertSame("userSample2", $content->ws_users[0]->name);
        $this->AssertSame("admin", $content->auth->username);
        $this->AssertTrue($content->auth->split);
        $this->AssertSame("1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123", $content->key);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
