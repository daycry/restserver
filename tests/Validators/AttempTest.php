<?php

namespace Daycry\RestServer\Tests\Validators;

use CodeIgniter\Config\Factories;
use CodeIgniter\HTTP\Request;
use CodeIgniter\HTTP\Response;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

use Daycry\RestServer\Database\Seeds\ExampleSeeder;

class AttempTest extends CIUnitTestCase
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
        $this->resetFactories();
        
        parent::setUp();

        $routes = [
            ['get', 'hello', '\Tests\Support\Controllers\Hello::index'],
            ['get', 'nohello', '\Tests\Support\Controllers\NoHello::index']
        ];
        
        $this->withRoutes($routes);

        $this->config = config('RestServer');
    }

    /** @var Request */
    protected function request()
    {
        return new Request(Factories::config('App'));
    }

    public function testRemovePastAttemp()
    {
        $this->withHeaders([
            'Origin' => 'https://test-cors.local',
            'X-API-KEY' => '33o8go0csckk8cckgw4kk40g4c4s0ckkcscggo12',
            'Content-Type' => 'application/json'
        ]);

        $result = $this->withBody(
            json_encode(['test' => 'hello'])
        )->call('get', 'hello');

        $content = \json_decode($result->getJson());

        $result->assertStatus(401);
        $this->assertObjectHasAttribute("error", $content->messages);
        $this->assertMatchesRegularExpression("/Invalid API key/i", $content->messages->error);
    }

    public function testErrorMaxAttempts()
    {
        $this->withHeaders([
            'Origin' => 'https://test-cors.local',
            'X-API-KEY' => '33o8go0csckk8cckgw4kk40g4c4s0ckkcscggo12',
            'Content-Type' => 'application/json'
        ]);

        $attemptModel = new \Daycry\RestServer\Models\AttemptModel();
        $attempt = $attemptModel->where('ip_address', $this->request()->getIPAddress())->first();
        $attempt->hour_started = time();
        $attemptModel->save($attempt);

        $result = $this->withBody(
            json_encode(['test' => 'hello'])
        )->call('get', 'hello');

        $content = \json_decode($result->getJson());

        $result->assertStatus(429);
        $this->assertObjectHasAttribute("error", $content->messages);
        $this->assertMatchesRegularExpression("/has reached the maximum of invalid requests/i", $content->messages->error);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}