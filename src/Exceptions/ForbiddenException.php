<?php namespace Daycry\RestServer\Exceptions;

class ForbiddenException extends \RuntimeException implements \Daycry\RestServer\Interfaces\ForbiddenInterface
{
    protected $code = 403;

    public static function forUnsupportedProtocol()
    {
        return new self( lang( 'Rest.textRestUnsupported' ) );
    }
}
