<?php namespace Daycry\RestServer\Exceptions;

use CodeIgniter\Exceptions\FrameworkException;

class ForbiddenException extends FrameworkException implements ForbiddenInterface
{
    //protected $code = 403;

    public static function forUnsupportedProtocol()
    {
        return new self( lang( 'Rest.textRestUnsupported' ) );
    }
}
