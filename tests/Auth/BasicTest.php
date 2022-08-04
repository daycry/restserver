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
            ['get', 'helloauthbasic', '\Tests\Support\Controllers\HelloAuthBasic::index'],
            ['get', 'helloauthbasic/invalid', '\Tests\Support\Controllers\HelloAuthBasic::invalid'],
            ['get', 'helloauthbasic/validateParams', '\Tests\Support\Controllers\HelloAuthBasic::validateParams'],
            ['get', 'helloauthbasicajax', '\Tests\Support\Controllers\HelloAuthBasicAjax::index']
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

        $result->assertStatus(400);
        $this->assertObjectHasAttribute("error", $content->messages);
        $this->assertStringStartsWith("Cannot modify header information", $content->messages->error);
    }

    public function testBasicInvalidUsernameError()
    {
        $this->withHeaders([
            'Origin' => 'https://test-cors.local',
            'X-API-KEY' => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
            'Authorization' => 'Basic ' . \base64_encode('admin1:1234')
        ]);

        $result = $this->withBody(
            json_encode(['test' => 'helloauthbasic'])
        )->call('get', 'helloauthbasic?format=json');

        $content = \json_decode( $result->getJson() );

        $result->assertStatus(400);
        $this->assertObjectHasAttribute("error", $content->messages);
        $this->assertStringStartsWith("Cannot modify header", $content->messages->error);
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
            'Content-Type' => 'application/json',
            'X-API-KEY' => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
            'Authorization' => 'Basic ' . \base64_encode('admin:1234')
        ]);

        $result = $this->withBody(
            json_encode(['test' => 'helloauthbasic'])
        )->call('get', 'helloauthbasic?tatiro=taa');


        $content = \json_decode( $result->getJson() );

        $result->assertStatus(200);
        $this->assertObjectHasAttribute("test", $content);
        $this->assertObjectHasAttribute("auth", $content);
        $this->assertObjectHasAttribute("key", $content);
        $this->assertObjectHasAttribute("user", $content);
        $this->assertIsArray($content->user);
        $this->assertObjectHasAttribute('name', $content->user[0]);
        $this->AssertSame("helloauthbasic", $content->test);
        $this->AssertSame("userSample2", $content->user[0]->name);
        $this->AssertSame("admin", $content->auth);
        $this->AssertSame("1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123", $content->key);
    }

    public function testBasicValidateParamsSuccess()
    {
        $this->withHeaders([
            'Origin' => 'https://test-cors.local',
            'X-API-KEY' => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
            'Authorization' => 'Basic ' . \base64_encode('admin:1234')
        ]);

        $result = $this->withBody(
            json_encode(['param' => 'helloauthbasic', 'method' => 'method'])
        )->call('get', 'helloauthbasic/validateParams');

        $content = \json_decode( $result->getJson() );

        $result->assertStatus(200);
        $this->assertObjectHasAttribute("param", $content);
        $this->assertObjectHasAttribute("method", $content);
        $this->assertObjectHasAttribute("auth", $content);
        $this->assertObjectHasAttribute("format", $content);
        $this->assertObjectHasAttribute("key", $content);
        $this->assertObjectHasAttribute("user", $content);
        $this->assertIsArray($content->user);
        $this->assertObjectHasAttribute('name', $content->user[0]);
        $this->AssertSame("helloauthbasic", $content->param);
        $this->AssertSame("method", $content->method);
        $this->AssertSame("userSample2", $content->user[0]->name);
        $this->AssertSame("admin", $content->auth);
        $this->AssertSame("json", $content->format);
        $this->AssertSame("1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123", $content->key);
    }

    public function testBasicFilterParamsError()
    {
        $this->withHeaders([
            'Origin' => 'https://test-cors.local',
            'X-API-KEY' => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
            'Authorization' => 'Basic ' . \base64_encode('admin:1234')
        ]);

        $result = $this->withBody(
            json_encode(['param' => 'helloauthbasic', 'method' => 'method', 'method1' => 'method1'])
        )->call('get', 'helloauthbasic/validateParams');

        $content = \json_decode( $result->getJson() );

        $result->assertStatus(403);
        $this->assertObjectHasAttribute("error", $content->messages);
        $this->assertStringStartsWith("Invalid params for", $content->messages->error);
    }

    public function testBasicValidateParamsError()
    {
        $this->withHeaders([
            'Origin' => 'https://test-cors.local',
            'X-API-KEY' => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
            'Authorization' => 'Basic ' . \base64_encode('admin:1234')
        ]);

        $result = $this->withBody(
            json_encode(['param' => '', 'method' => 'method'])
        )->call('get', 'helloauthbasic/validateParams');

        $content = \json_decode( $result->getJson() );

        $result->assertStatus(400);
        $this->assertObjectHasAttribute("param", $content->messages);
        $this->assertStringStartsWith("The param field is required.", $content->messages->param);
    }

    public function testBasicInvalidMethodError()
    {
        $this->withHeaders([
            'Origin' => 'https://test-cors.local',
            'X-API-KEY' => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
            'Authorization' => 'Basic ' . \base64_encode('admin:12345')
        ]);

        $result = $this->withBody(
            json_encode(['test' => 'helloauthbasic'])
        )->call('get', 'helloauthbasic/invalid');

        $content = \json_decode( $result->getJson() );

        $result->assertStatus(403);
        $this->assertObjectHasAttribute("error", $content->messages);
        $this->assertStringStartsWith("Invalid method:", $content->messages->error);
    }

    public function testBasicAjaxError()
    {
        $this->withHeaders([
            'Origin' => 'https://test-cors.local',
            'X-API-KEY' => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
            'Authorization' => 'Basic ' . \base64_encode('admin:12345')
        ]);

        $result = $this->withBody(
            json_encode(['test' => 'helloauthbasicajax'])
        )->call('get', 'helloauthbasicajax');

        $content = \json_decode( $result->getJson() );

        $result->assertStatus(403);
        $this->assertObjectHasAttribute("error", $content->messages);
        $this->AssertSame("Only AJAX requests are allowed", $content->messages->error);
    }

    public function testBasicAjaxSuccess()
    {
        $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Origin' => 'https://test-cors.local',
            'X-API-KEY' => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
            'Authorization' => 'Basic ' . \base64_encode('admin:1234')
        ]);

        $result = $this->withBody(
            json_encode(['test' => 'helloauthbasicajax'])
        )->call('get', 'helloauthbasicajax');

        $content = \json_decode( $result->getJson() );

        $result->assertStatus(200);
        $this->assertObjectHasAttribute("test", $content);
        $this->assertObjectHasAttribute("auth", $content);
        $this->assertObjectHasAttribute("key", $content);
        $this->assertObjectHasAttribute("user", $content);
        $this->assertIsArray($content->user);
        $this->assertObjectHasAttribute('name', $content->user[0]);
        $this->AssertSame("helloauthbasicajax", $content->test);
        $this->AssertSame("userSample2", $content->user[0]->name);
        $this->AssertSame("admin", $content->auth);
        $this->AssertSame("1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123", $content->key);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}