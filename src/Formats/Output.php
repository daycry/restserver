<?php

namespace Daycry\RestServer\Formats;

use CodeIgniter\HTTP\RequestInterface;

class Output
{
    public static function check(RequestInterface $request)
    {
        return $request->negotiate('media', config('Format')->supportedResponseFormats);
    }
}
