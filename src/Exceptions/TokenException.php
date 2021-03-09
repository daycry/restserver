<?php namespace Daycry\RestServer\Exceptions;

class TokenException extends \RuntimeException implements TokenInterface
{
    public static function forTokenExpired()
    {
        return new self( lang( 'Rest.tokenExpired' ) );
    }

    public static function forTokenUnauthorized()
    {
        return new self( lang( 'Rest.tokenUnauthorized' ) );
    }
}
