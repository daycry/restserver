<?php

namespace Tests\Support\Libraries;

use Daycry\RestServer\Interfaces\LibraryAuthInterface;
use CodeIgniter\Config\BaseConfig;

class LibraryBasicAuth implements LibraryAuthInterface
{
    public function validate( $username, $password = true )
    {
        if( $username != 'admin' )
        {
            throw \Daycry\RestServer\Exceptions\UnauthorizedException::forInvalidCredentials();
        }

        return $username;
    }
}