<?php namespace Daycry\RestServer\Exceptions;

use CodeIgniter\Exceptions\FrameworkException;

class UserException extends FrameworkException
{
    /**
     * Thrown when the user class does not extends of userAbstract
     *
     * @return static
     */
    public static function forInvalidUserClass()
    {
        return new self( lang( 'Rest.textInvalidUserClassConfiguration' ) );
    }
}
