<?php namespace Daycry\RestServer\Exceptions;

use CodeIgniter\Exceptions\FrameworkException;

//class UnauthorizedException extends \RuntimeException implements UnauthorizedInterface
class UnauthorizedException extends FrameworkException implements UnauthorizedInterface
{
    //protected $code = 401;

    public static function forTokenExpired()
    {
        return new self( lang( 'Rest.tokenExpired' ) );
    }

    public static function forTokenUnauthorized()
    {
        return new self( lang( 'Rest.tokenUnauthorized' ) );
    }

    public static function forInvalidApiKey( $apiKey )
    {
        $parser = \Config\Services::parser();
        return new self( $parser->setData( array( 'key' => $apiKey ) )->renderString( lang( 'Rest.textRestInvalidApiKey' ) ) );
    }

    public static function forInvalidCredentials()
    {
        return new self( lang( 'Rest.textRestInvalidCredentials' ) );
    }

    public static function forUnauthorized()
    {
        return new self( lang( 'Rest.textUnauthorized' ) );
    }

    public static function forIpDenied()
    {
        return new self( lang( 'Rest.ipDenied' ) );
    }

    public static function forApiKeyLimit()
    {
        return new self( lang( 'Rest.textRestApiKeyTimeLimit' ) );
    }

    public static function forApiKeyPermissions()
    {
        return new self( lang( 'Rest.textRestApiKeyPermissions' ) );
    }

    public static function forIpAddressTimeLimit()
    {
        return new self( lang( 'Rest.textRestIpAddressTimeLimit' ) );
    }
}
