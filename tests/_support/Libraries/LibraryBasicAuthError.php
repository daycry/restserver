<?php

namespace Tests\Support\Libraries;

class LibraryBasicAuthError
{
    public function validate($username, $password = true)
    {
        if ($username != 'admin') {
            throw \Daycry\RestServer\Exceptions\UnauthorizedException::forInvalidCredentials();
        }

        return $username;
    }
}
