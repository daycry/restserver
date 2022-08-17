<?php

namespace Tests\Auth;

use CodeIgniter\Config\Factories;
use CodeIgniter\HTTP\Request;
use CodeIgniter\HTTP\Response;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

use Daycry\RestServer\Database\Seeds\ExampleSeeder;

class DigestTest extends CIUnitTestCase
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
            ['get', 'helloauthdigest', '\Tests\Support\Controllers\HelloAuthDigest::index']
        ];

        $this->withRoutes($routes);

        $this->config = config('RestServer');
    }

    public function testDigestError()
    {
        $this->withHeaders([
            'Origin' => 'https://test-cors.local',
            'X-API-KEY' => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
            'Authorization' => 'Digest username="admin1", nonce="62e93c9a89415", uri="/helloauthdigest", response="01c565231b4572c6608aa5e0857877bd", qop="auth", nc="00000002", cnonce="264e5043000b4bda"'
        ]);

        $result = $this->withBody(
            json_encode(['test' => 'helloauthdigest'])
        )->call('get', 'helloauthdigest?format=json');

        $content = \json_decode($result->getJson());

        $result->assertStatus(401);
        $this->assertObjectHasAttribute("error", $content->messages);
        $this->AssertSame("Invalid credentials", $content->messages->error);
    }

    public function testDigestErrorNoUsername()
    {
        $this->withHeaders([
            'Origin' => 'https://test-cors.local',
            'X-API-KEY' => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
            'Authorization' => 'Digest username="", nonce="", uri="/helloauthdigest", response="", qop="auth", nc="", cnonce=""'
        ]);

        $result = $this->withBody(
            json_encode(['test' => 'helloauthdigest'])
        )->call('get', 'helloauthdigest?format=json');

        $content = \json_decode($result->getJson());

        $result->assertStatus(400);
        $this->assertObjectHasAttribute("error", $content->messages);
    }

    public function testDigestSuccess()
    {
        $this->withHeaders([
            'Origin' => 'https://test-cors.local',
            'X-API-KEY' => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
            'Authorization' => 'Digest username="admin", nonce="62e93c9a89415", uri="/helloauthdigest", response="01c565231b4572c6608aa5e0857877bd", qop="auth", nc="00000002", cnonce="264e5043000b4bda"'
        ]);

        $result = $this->withBody(
            json_encode(['test' => 'helloauthdigest'])
        )->call('get', 'helloauthdigest');


        $content = \json_decode($result->getJson());

        $result->assertStatus(200);
        $this->assertObjectHasAttribute("test", $content);
        $this->assertObjectHasAttribute("auth", $content);
        $this->assertObjectHasAttribute("key", $content);
        $this->assertObjectHasAttribute("user", $content);
        $this->assertIsArray($content->user);
        $this->assertObjectHasAttribute('name', $content->user[0]);
        $this->AssertSame("helloauthdigest", $content->test);
        $this->AssertSame("userSample2", $content->user[0]->name);
        $this->AssertSame(md5("admin:" . $this->config->restRealm . ':1234'), $content->auth);
        $this->AssertSame("1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123", $content->key);
    }

    public function testDigestEmptyUsername()
    {
        $this->withHeaders([
            'Origin' => 'https://test-cors.local',
            'X-API-KEY' => '1238go0csckk8cckgw4kk40g4c4s0ckkcscgg123',
            'Authorization' => ''
        ]);

        $result = $this->withBody(
            json_encode(['test' => 'helloauthdigest'])
        )->call('get', 'helloauthdigest');

        $content = \json_decode($result->getJson());

        $result->assertStatus(400);
        $this->assertObjectHasAttribute("error", $content->messages);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
