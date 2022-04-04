<?php

namespace Daycry\RestServer\Libraries\Auth;

use Daycry\RestServer\Interfaces\AuthInterface;
use Daycry\RestServer\Exceptions\UnauthorizedException;

class SessionAuth extends BaseAuth implements AuthInterface
{
    public function __construct()
    {
        $this->method = 'session';
        parent::__construct();
    }

    public function validate()
    {
        // Load library session of CodeIgniter
        $session = \Config\Services::session();

        // If false, then the user isn't logged in
        if (!$session->get($this->restConfig->authSource)) {
            $this->isValidRequest = false;
            throw UnauthorizedException::forUnauthorized();
        }

        return $session->get($this->restConfig->authSource);
    }
}
