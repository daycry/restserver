<?php

namespace Daycry\RestServer\Validators;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Router\Router;
use CodeIgniter\Config\Factories;

class Cors
{
    public static function check(RequestInterface $request, Router $router, ResponseInterface &$response)
    {
        // Convert the config items into strings
        $allowed_headers = implode(', ', config('RestServer')->allowedCorsHeaders);
        $allowed_methods = implode(', ', config('RestServer')->allowedCorsMethods);

        if (self::_isCorsRequest($request)) {
            // If we want to allow any domain to access the API
            if (config('RestServer')->allowAnyCorsDomain === true) {
                $response->setHeader('Access-Control-Allow-Origin', '*');
                $response->setHeader('Access-Control-Allow-Headers', $allowed_headers);
                $response->setHeader('Access-Control-Allow-Methods', $allowed_methods);
            } else {
                $origin = $request->getHeaderLine('origin');

                // If the origin domain is in the allowed_cors_origins list, then add the Access Control headers
                if (in_array($origin, config('RestServer')->allowedCorsOrigins)) {
                    $response->setHeader('Access-Control-Allow-Origin', $origin);
                    $response->setHeader('Access-Control-Allow-Headers', $allowed_headers);
                    $response->setHeader('Access-Control-Allow-Methods', $allowed_methods);
                }
            }

            // If there are headers that should be forced in the CORS check, add them now
            if (is_array(config('RestServer')->forcedCorsHeaders)) {
                foreach (config('RestServer')->forcedCorsHeaders as $header => $value) {
                    $response->setHeader($header, $value);
                }
            }
        }

        // If the request HTTP method is 'OPTIONS', kill the response and send it to the client
        if ($request->getMethod() === 'options') {
            exit;
        }
    }

    private static function _isSameHost(RequestInterface $request): bool
    {
        return $request->getHeaderLine('origin') === Factories::config('App')->baseURL;
    }

    private static function _isCorsRequest(RequestInterface $request): bool
    {
        return $request->hasHeader('origin') && !self::_isSameHost($request);
    }
}
