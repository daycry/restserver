<?php namespace Daycry\RestServer\Exceptions;

class ForbiddenException extends \RuntimeException implements \Daycry\RestServer\Interfaces\ForbiddenInterface
{
    protected $code = 403;

    public static function forUnsupportedProtocol()
    {
        return new self( lang( 'Rest.textRestUnsupported' ) );
    }

    public static function forOnlyAjax()
    {
        return new self( lang( 'Rest.textRestAjaxOnly' ) );
    }

    public static function validationtMethodParamsError()
    {
        return new self( lang( 'Rest.textInvalidMethodParams' ) );
    }
}
