<?php

namespace Daycry\RestServer\Formats;

use CodeIgniter\HTTP\RequestInterface;

class Input
{
    public static function check(RequestInterface $request)
    {
        $result = null;
        $content_type = $request->getHeaderLine('content-type');

        if (empty($content_type) === false) {
            foreach (config('Format')->supportedResponseFormats as $type) {
                // $type = mime type e.g. application/json
                if ($content_type === $type) {
                    $result = $type;
                }
            }
        }

        if ($result === null) {
            $result = $request->negotiate('media', config('Format')->supportedResponseFormats);
        }


        return $result;
    }
}
