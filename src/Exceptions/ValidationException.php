<?php namespace Daycry\RestServer\Exceptions;

class ValidationException extends \RuntimeException implements \Daycry\RestServer\Interfaces\ValidationInterface
{
    protected $code = 400;

    public static function validationError()
    {
        return new self( "" );
    }
}
