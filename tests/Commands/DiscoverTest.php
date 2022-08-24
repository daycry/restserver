<?php

namespace Tests\Commands;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;
use CodeIgniter\Test\DatabaseTestTrait;

use Daycry\RestServer\Database\Seeds\ExampleSeeder;

class DiscoverTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = false;
    protected $refresh     = true;
    protected $seedOnce = false;
    protected $seed = ExampleSeeder::class;
    protected $basePath = HOMEPATH . 'src/Database';
    protected $namespace = 'Daycry\RestServer';
    
    /**
     * @var resource
     */
    private $streamFilter;

    /**
     * @var BaseConfig
     */
    private BaseConfig $config;
    
    protected function setUp(): void
    {
        parent::setUp();

        CITestStreamFilter::$buffer = '';
        $this->streamFilter = stream_filter_append(STDOUT, 'CITestStreamFilter');
        $this->streamFilter = stream_filter_append(STDERR, 'CITestStreamFilter');

        $this->config = new \Daycry\RestServer\Config\RestServer();
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
    }

    public function testCommandDiscover()
    {
        $this->assertTrue(true);
        //command( 'restserver:discover' );
        //$result = CITestStreamFilter::$buffer;

        /*$data = \json_decode( file_get_contents( $this->config->filePath . $this->config->fileName ), true );

        $this->assertFileExists( $this->config->filePath . $this->config->fileName );
        $this->assertSame( $this->message, $data['message'] );
        $this->assertTrue( $this->_arrays_are_similar( \explode( ' ', $this->ip ), $data[ 'allowed_ips' ] ) );*/
    }


    /**
     * Determine if two associative arrays are similar
     *
     * Both arrays must have the same indexes with identical values
     * without respect to key ordering 
     * 
     * @param array $a
     * @param array $b
     * @return bool
     */
    private function _arrays_are_similar( $a, $b )
    {
        // if the indexes don't match, return immediately
        if( count( array_diff_assoc( $a, $b ) ) )
        {
            return false;
        }

        // we know that the indexes, but maybe not values, match.
        // compare the values between the two arrays
        foreach( $a as $k => $v )
        {
            if( $v !== $b[ $k ] )
            {
                return false;
            }
        }
        // we have identical indexes, and no unequal values
        return true;
    }

    protected function tearDown(): void
    {
        stream_filter_remove($this->streamFilter);
    }
}