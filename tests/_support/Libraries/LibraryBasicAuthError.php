<?php

namespace Tests\Support\Libraries;

class LibraryBasicAuthError
{
    public function validate( $username, $password = true )
    {
        var_dump( $username );exit;
        if( $username != 'admin' )
        {
            throw \Daycry\RestServer\Exceptions\UnauthorizedException::forInvalidCredentials();
        }

        return $username;
    }
}