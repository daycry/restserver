<?php

namespace Daycry\RestServer\Libraries\Auth;

use Daycry\RestServer\Interfaces\LibraryAuthInterface;
use Daycry\RestServer\Exceptions\UnauthorizedException;

abstract class BaseAuth
{
    protected $restConfig = null;

    protected $ipAllow = true;

    protected $request = true;

    protected $method = null;

    public function __construct()
    {
        $this->restConfig = config('RestServer');
        $this->request = service('request');
    }

    public function getIpAllow()
    {
        return $this->ipAllow;
    }

    /**
     * Check if the user is logged in
     *
     * @access protected
     * @param string $username The user's name
     * @param bool|string $password The user's password
     * @return bool
     */
    protected function checkLogin($username = null, $password = false)
    {
        if (empty($username)) {
            return false;
        }

        $auth_source = \strtolower($this->restConfig->authSource);
        $rest_auth = \strtolower($this->method);
        $valid_logins = $this->restConfig->restValidLogins;

        if ($auth_source !== 'library' && $rest_auth === 'digest') {
            // For digest we do not have a password passed as argument
            return md5($username . ':' . $this->restConfig->restRealm . ':' . (isset($valid_logins[ $username ]) ? $valid_logins[ $username ] : ''));
        }

        if ($auth_source !== 'library' && $rest_auth === 'bearer') {
            $jwtLibrary = new \Daycry\RestServer\Libraries\JWT();
            $claims = $jwtLibrary->decode($username);

            if (!$claims || !isset( $valid_logins[ $claims->get('data') ] )) {
                throw \Daycry\RestServer\Exceptions\UnauthorizedException::forInvalidCredentials();
            }

            return $claims->get('data');
        }

        if ($password === false) {
            return false;
        }

        if ($auth_source === 'library') {
            log_message('debug', "Performing Library authentication for $username");

            return $this->_performLibraryAuth($username, $password);
        }

        if (array_key_exists($username, $valid_logins) === false) {
            return false;
        }

        if ($valid_logins[ $username ] !== $password) {
            throw \Daycry\RestServer\Exceptions\UnauthorizedException::forInvalidCredentials();
        }

        return $username;
    }

    /**
     * Force logging in by setting the WWW-Authenticate header
     *
     * @access protected
     * @param string $nonce A server-specified data string which should be uniquely generated each time
     * @return void
     */
    protected function forceLogin($nonce = '')
    {
        $rest_auth = \strtolower($this->method);
        $rest_realm = $this->restConfig->restRealm;

        if (strtolower($rest_auth) === 'basic') {
            // See http://tools.ietf.org/html/rfc2617#page-5
            header('WWW-Authenticate: Basic realm="' . $rest_realm . '"');
        } elseif (strtolower($rest_auth) === 'digest') {
            // See http://tools.ietf.org/html/rfc2617#page-18
            header(
                'WWW-Authenticate: Digest realm="' . $rest_realm
                . '", qop="auth", nonce="' . $nonce
                . '", opaque="' . md5($rest_realm) . '"'
            );
        }

        if ($this->restConfig->strictApiAndAuth === true) {
            throw \Daycry\RestServer\Exceptions\UnauthorizedException::forInvalidCredentials();
        }
    }

    protected function _performLibraryAuth($username = '', $password = null)
    {
        $authLibraryClass = $this->restConfig->authLibraryClass;

        if (!isset($authLibraryClass[ $this->method ]) || !\class_exists($authLibraryClass[ $this->method ])) {
            log_message('critical', 'Library Auth: Failure, ' . $this->method . ' does not exist');
            return false;
        }

        $authLibraryFunction = $this->restConfig->authLibraryFunction;

        $authLibraryClass = new $authLibraryClass[ $this->method ]();

        if (empty($authLibraryClass) || ($authLibraryClass instanceof LibraryAuthInterface === false)) {
            log_message('critical', 'Library Auth: Failure, empty authLibraryClass');
            return false;
        }

        if (empty($authLibraryFunction)) {
            log_message('critical', 'Library Auth: Failure, empty authLibraryFunction');
            return false;
        }

        if (\is_callable([ $authLibraryClass, $authLibraryFunction ])) {
            return $authLibraryClass->{$authLibraryFunction}($username, $password);
        }

        return false;
    }
}
