<?php namespace Daycry\RestServer\Exceptions;

class ForbiddenException extends \RuntimeException implements ForbiddenInterface
{
    public static function forUnsupportedProtocol()
    {
        return new self( lang( 'Rest.textRestUnsupported' ) );
    }
}
