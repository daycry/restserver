<?php

namespace Daycry\RestServer\Tests\Auth;

use CodeIgniter\Config\Factories;
use CodeIgniter\HTTP\Request;
use CodeIgniter\HTTP\Response;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

use Daycry\RestServer\Database\Seeds\ExampleSeeder;

class SessionTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

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
            ['get', 'helloauthsession', '\Tests\Support\Controllers\HelloAuthSession::index']
        ];

        $this->withRoutes($routes);

        $this->config = config('RestServer');
    }

    public function testSessionError()
    {
        $this->withHeaders([
            'X-API-KEY' => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123'
        ]);

        $result = $this->withBody(
            json_encode(['test' => 'helloauthsession'])
        )->call('get', 'helloauthsession');


        $content = \json_decode($result->getJson());

        $result->assertStatus(401);
        $this->assertObjectHasAttribute("error", $content->messages);
        $this->AssertSame("Invalid credentials", $content->messages->error);
    }

    public function testSessionSuccess()
    {
        $values = [
            'sessionTest' => 'admin',
        ];

        $this->withSession($values);


        $this->withHeaders([
            'X-API-KEY' => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123'
        ]);

        $result = $this->withBody(
            json_encode(['test' => 'helloauthsession'])
        )->call('get', 'helloauthsession');

        $content = \json_decode($result->getJson());

        $result->assertStatus(200);
        $this->assertObjectHasAttribute("test", $content);
        $this->assertObjectHasAttribute("auth", $content);
        $this->assertObjectHasAttribute("key", $content);
        $this->assertObjectHasAttribute("user", $content);
        $this->assertIsArray($content->user);
        $this->assertObjectHasAttribute('name', $content->user[0]);
        $this->AssertSame("helloauthsession", $content->test);
        $this->AssertSame("userSample2", $content->user[0]->name);
        $this->AssertSame("admin", $content->auth);
        $this->AssertSame("1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123", $content->key);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
