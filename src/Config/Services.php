<?php namespace Daycry\RestServer\Config;

use CodeIgniter\Config\BaseService;
use Daycry\RestServer\RestServer;

class Services extends BaseService
{
    public static function restserver( bool $getShared = true )
    {
		if ( $getShared )
		{
			return static::getSharedInstance( 'restserver' );
		}

		$config = config( 'RestServer' );

		return new RestServer( $config );
	}
}