<?php namespace Daycry\RestServer\Exceptions;

class ForbiddenException extends \RuntimeException implements ForbiddenInterface
{
    //protected $code = 403;

    public static function forUnsupportedProtocol()
    {
        return new self( lang( 'Rest.textRestUnsupported' ) );
    }
}
