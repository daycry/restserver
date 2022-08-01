<?php

namespace Daycry\RestServer\Libraries\Auth;

use Daycry\RestServer\Interfaces\AuthInterface;
use Daycry\RestServer\Exceptions\UnauthorizedException;

class BasicAuth extends BaseAuth implements AuthInterface
{
    public function __construct()
    {
        $this->method = 'basic';
        parent::__construct();
    }

    public function validate()
    {
        $username = $this->request->getServer('PHP_AUTH_USER');
        //$http_auth = $this->request->getServer('HTTP_AUTHENTICATION') ?: $this->request->getServer('HTTP_AUTHORIZATION');
        $http_auth = $this->request->getHeaderLine('authentication') ?: $this->request->getHeaderLine('authorization');

        $password = null;
        if ($username !== null) {
            $password = $this->request->getServer('PHP_AUTH_PW');
        } elseif ($http_auth !== null) {
            // If the authentication header is set as basic, then extract the username and password from
            // HTTP_AUTHORIZATION e.g. my_username:my_password. This is passed in the .htaccess file
            if (strpos(strtolower($http_auth), 'basic') === 0) {
                // Search online for HTTP_AUTHORIZATION workaround to explain what this is doing
                list($username, $password) = explode(':', base64_decode(substr($this->request->getHeaderLine('authorization'), 6)));
            }
        }

        // Check if the user is logged into the system
        $username = $this->checkLogin($username, $password);

        if ($username === false) {
            $this->forceLogin();
            throw UnauthorizedException::forInvalidCredentials();
        }

        return $username;
    }
}
