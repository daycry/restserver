<?php namespace Daycry\RestServer\Exceptions;

use CodeIgniter\Exceptions\FrameworkException;

class ValidationException extends FrameworkException implements ValidationInterface
{
    //protected $code = 400;

    public static function validationError()
    {
        return new self( "" );
    }
}
