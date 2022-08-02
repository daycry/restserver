<?php

namespace Daycry\RestServer\Tests\Auth;

use CodeIgniter\Config\Factories;
use CodeIgniter\HTTP\Request;
use CodeIgniter\HTTP\Response;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

use Daycry\RestServer\Database\Seeds\ExampleSeeder;

class BasicTest extends CIUnitTestCase
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
            ['get', 'helloauthbasic', '\Tests\Support\Controllers\HelloAuthBasic::index']
        ];
        
        $this->withRoutes($routes);

        $this->config = config('RestServer');
    }

    public function testBasicError()
    {
        $this->withHeaders([
            'Origin' => 'https://test-cors.local',
            'X-API-KEY' => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
            'Authorization' => 'Basic ' . \base64_encode('admin:12345')
        ]);

        $result = $this->withBody(
            json_encode(['test' => 'helloauthbasic'])
        )->call('get', 'helloauthbasic?format=json');

        $content = \json_decode( $result->getJson() );

        $result->assertStatus(401);
        $this->assertObjectHasAttribute("error", $content->messages);
        $this->AssertSame("Invalid credentials", $content->messages->error);
    }

    public function testBasicErrorNoUsername()
    {
        $this->withHeaders([
            'Origin' => 'https://test-cors.local',
            'X-API-KEY' => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
            'Authorization' => 'Basic ' . \base64_encode(':12345')
        ]);

        $result = $this->withBody(
            json_encode(['test' => 'helloauthbasic'])
        )->call('get', 'helloauthbasic?format=json1');

        $content = \json_decode( $result->getJson() );

        $result->assertStatus(400);
        $this->assertObjectHasAttribute("error", $content->messages);
        $this->assertStringStartsWith("Cannot modify header", $content->messages->error);
    }

    public function testBasicErrorNoPassword()
    {
        $this->withHeaders([
            'Origin' => 'https://test-cors.local',
            'X-API-KEY' => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
            'Authorization' => 'Basic ' . \base64_encode('admin:')
        ]);

        $result = $this->withBody(
            json_encode(['test' => 'helloauthbasic'])
        )->call('get', 'helloauthbasic');

        $content = \json_decode( $result->getJson() );

        $result->assertStatus(400);
        $this->assertObjectHasAttribute("error", $content->messages);
        $this->assertStringStartsWith("Cannot modify header", $content->messages->error);
    }

    public function testBasicSuccess()
    {
        $this->withHeaders([
            'Origin' => 'https://test-cors.local',
            'X-API-KEY' => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
            'Authorization' => 'Basic ' . \base64_encode('admin:1234')
        ]);

        $result = $this->withBody(
            json_encode(['test' => 'helloauthbasic'])
        )->call('get', 'helloauthbasic');


        $content = \json_decode( $result->getJson() );

        $result->assertStatus(200);
        $this->assertObjectHasAttribute("test", $content);
        $this->assertObjectHasAttribute("auth", $content);
        $this->assertObjectHasAttribute("key", $content);
        $this->AssertSame("helloauthbasic", $content->test);
        $this->AssertSame("admin", $content->auth);
        $this->AssertSame("1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123", $content->key);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}