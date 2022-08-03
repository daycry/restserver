<?php

namespace Daycry\RestServer\Validators;

use CodeIgniter\HTTP\RequestInterface;

class WhiteList
{
    public static function check(RequestInterface $request): bool
    {
        $response = true;

        $whitelist = explode(',', config('RestServer')->restIpWhitelist);
        array_push($whitelist, '127.0.0.1', '0.0.0.0');

        foreach ($whitelist as &$ip) {
            $ip = trim($ip);
        }

        if (in_array($request->getIPAddress(), $whitelist) === false) {
            // @codeCoverageIgnoreStart
            $response = false;
            // @codeCoverageIgnoreEnd
        }

        return $response;
    }
}
