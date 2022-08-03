<?php

namespace Tests\Support\Libraries;

use Daycry\RestServer\Interfaces\LibraryAuthInterface;
use CodeIgniter\Config\BaseConfig;

class LibraryBearerAuth implements LibraryAuthInterface
{
    public function validate( $username, $password = true )
    {
        $jwtLibrary = new \Daycry\RestServer\Libraries\JWT();
        $claims = $jwtLibrary->decode($username);

        if( $claims->get('username') != 'admin' )
        {
            throw \Daycry\RestServer\Exceptions\UnauthorizedException::forInvalidCredentials();
        }

        return array( 'username' => $claims->get('username'), 'split' => $claims->get('split') );
    }
}