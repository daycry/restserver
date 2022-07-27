<?php

namespace Daycry\RestServer\Validators;

use CodeIgniter\HTTP\RequestInterface;

class BlackList
{
    public static function check(RequestInterface $request): bool
    {
        $response = true;
        // Match an ip address in a blacklist e.g. 127.0.0.0, 0.0.0.0
        $pattern = sprintf('/(?:,\s*|^)\Q%s\E(?=,\s*|$)/m', $request->getIPAddress());

        if (preg_match($pattern, config('RestServer')->restIpBlacklist)) {
            $response = false;
        }

        return $response;
    }
}
