<?php

namespace Daycry\RestServer\Libraries\Auth;

use Daycry\RestServer\Interfaces\AuthInterface;
use Daycry\RestServer\Exceptions\UnauthorizedException;

class DigestAuth extends BaseAuth implements AuthInterface
{
    public function __construct()
    {
        $this->method = 'digest';
        parent::__construct();
    }

    public function validate()
    {
        $digest_string = $this->request->getServer('PHP_AUTH_DIGEST');
        if ($digest_string === null) {
            $digest_string = $this->request->getHeaderLine('authorization');
        }

        $unique_id = uniqid();

        // The $_SESSION['error_prompted'] variable is used to ask the password
        // again if none given or if the user enters wrong auth information
        $digest_string = $digest_string . '';
        if (empty($digest_string)) {
            $this->forceLogin($unique_id);
        }

        $matches = [];
        preg_match_all('@(username|nonce|uri|nc|cnonce|qop|response)=[\'"]?([^\'",]+)@', $digest_string, $matches);
        $digest = (empty($matches[1]) || empty($matches[2])) ? [] : array_combine($matches[1], $matches[2]);

        // For digest authentication the library function should return already stored md5(username:restrealm:password) for that username see rest.php::auth_library_function config
        $digest['username'] = isset( $digest['username'] ) ? $digest['username'] : null;
        $username = $this->checkLogin($digest['username'], true);

        if (isset($digest['username']) === false || $username === false) {
            $this->forceLogin($unique_id);
        }

        $md5 = md5(strtoupper($this->request->getMethod()) . ':' . $digest['uri']);
        $valid_response = md5($username.':'.$digest['nonce'].':'.$digest['nc'].':'.$digest['cnonce'].':'.$digest['qop'].':'.$md5);

        if (strcasecmp($digest['response'], $valid_response) !== 0) {
            throw UnauthorizedException::forInvalidCredentials();
        }

        return $username;
    }
}
