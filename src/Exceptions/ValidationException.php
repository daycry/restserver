<?php namespace Daycry\RestServer\Exceptions;

class ValidationException extends \RuntimeException implements ValidationInterface
{
    public static function validationError()
    {
        return new self( "" );
    }
}
