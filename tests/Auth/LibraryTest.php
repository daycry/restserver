<?php

namespace Tests\Auth;

use CodeIgniter\Config\Factories;
use CodeIgniter\HTTP\Request;
use CodeIgniter\HTTP\Response;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

use Daycry\RestServer\Database\Seeds\ExampleSeeder;

class LibraryTest extends CIUnitTestCase
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
            ['get', 'helloauthlibrary', '\Tests\Support\Controllers\HelloAuthLibrary::index'],
            ['get', 'helloauthlibraryerror', '\Tests\Support\Controllers\HelloAuthLibraryError::index']
        ];

        $this->withRoutes($routes);

        $this->config = config('RestServer');
    }

    public function testLibraryError()
    {
        $this->withHeaders([
            'Origin' => 'https://test-cors.local',
            'X-API-KEY' => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
            'Authorization' => 'Basic ' . \base64_encode('admin:1234')
        ]);

        $result = $this->withBody(
            json_encode(['test' => 'helloauthlibraryerror'])
        )->call('get', 'helloauthlibraryerror?format=json');

        $content = \json_decode($result->getJson());

        $result->assertStatus(403);
        $this->assertObjectHasAttribute("error", $content->messages);
        $this->AssertSame("Invalid library implementation", $content->messages->error);
    }

    public function testLibraryErrorNoUsername()
    {
        $this->withHeaders([
            'Origin' => 'https://test-cors.local',
            'X-API-KEY' => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
            'Authorization' => 'Basic ' . \base64_encode(':12345')
        ]);

        $result = $this->withBody(
            json_encode(['test' => 'helloauthlibrary'])
        )->call('get', 'helloauthlibrary?format=json1');

        $content = \json_decode($result->getJson());

        $result->assertStatus(400);
        $this->assertObjectHasAttribute("error", $content->messages);
        $this->assertStringStartsWith("Cannot modify header", $content->messages->error);
    }

    public function testLibraryErrorNoPassword()
    {
        $this->withHeaders([
            'Origin' => 'https://test-cors.local',
            'X-API-KEY' => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
            'Authorization' => 'Basic ' . \base64_encode('admin:')
        ]);

        $result = $this->withBody(
            json_encode(['test' => 'helloauthlibrary'])
        )->call('get', 'helloauthlibrary');

        $content = \json_decode($result->getJson());

        $result->assertStatus(400);
        $this->assertObjectHasAttribute("error", $content->messages);
        $this->assertStringStartsWith("Cannot modify header", $content->messages->error);
    }

    public function testLibrarySuccess()
    {
        $this->withHeaders([
            'Origin' => 'https://test-cors.local',
            'X-API-KEY' => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
            'Authorization' => 'Basic ' . \base64_encode('admin:1234')
        ]);

        $result = $this->withBody(
            json_encode(['test' => 'helloauthlibrary'])
        )->call('get', 'helloauthlibrary');


        $content = \json_decode($result->getJson());

        $result->assertStatus(200);
        $this->assertObjectHasAttribute("test", $content);
        $this->assertObjectHasAttribute("auth", $content);
        $this->assertObjectHasAttribute("key", $content);
        $this->assertObjectHasAttribute("ws_users", $content);
        $this->assertIsArray($content->ws_users);
        $this->assertObjectHasAttribute('name', $content->ws_users[0]);
        $this->AssertSame("helloauthlibrary", $content->test);
        $this->AssertSame("userSample2", $content->ws_users[0]->name);
        $this->AssertSame("admin", $content->auth);
        $this->AssertSame("1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123", $content->key);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
