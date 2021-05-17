<?php namespace Daycry\RestServer\Exceptions;

class UnauthorizedException extends \RuntimeException implements UnauthorizedInterface
{
    public static function forTokenExpired()
    {
        return new self( lang( 'Rest.tokenExpired' ) );
    }

    public static function forTokenUnauthorized()
    {
        return new self( lang( 'Rest.tokenUnauthorized' ) );
    }

    public static function forInvalidCredentials()
    {
        return new self( lang( 'Rest.textRestInvalidCredentials' ) );
    }

    public static function forUnauthorized()
    {
        return new self( lang( 'Rest.textUnauthorized' ) );
    }
}
