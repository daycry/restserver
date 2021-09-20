<?php
namespace Daycry\RestServer\Libraries\Auth;

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
        if( !$session->get( $this->restConfig->authSource ) )
        {
            $this->isValidRequest = false;
            return false;
        }

        return $session->get( $this->restConfig->authSource );

    }
}