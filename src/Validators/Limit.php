<?php

namespace Daycry\RestServer\Validators;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\Router\Router;

class Limit
{
    public static function check(RequestInterface $request, Router $router, ?object $apiUser = null, ?object $petition = null): bool
    {
        if ($petition) {
            // They are special, or it might not even have a limit
            if (isset($apiUser) && isset($apiUser->ignore_limits) && empty($apiUser->ignore_limits) === false) {
                // Everything is fine
                return true;
            }

            $api_key = isset($apiUser->key) ? $apiUser->key : null;

            switch (config('RestServer')->restLimitsMethod) {
                case 'IP_ADDRESS':
                    $api_key = $request->getIPAddress();
                    $limited_uri = 'ip-address:' . $request->getIPAddress();
                    break;

                case 'API_KEY':
                    $limited_uri = 'api-key:' . $apiUser->key;
                    break;

                case 'METHOD_NAME':
                    $limited_uri = 'method-name:' . $petition->controller . '::' . $petition->method;
                    break;

                case 'ROUTED_URL':
                default:
                    $limited_uri = 'uri:'.$request->getPath().':'.$request->getMethod(); // It's good to differentiate GET from PUT
                    break;
            }

            if (is_numeric($petition->limit) === false) {
                // Everything is fine
                return true;
            }

            // How many times can you get to this method in a defined time_limit (default: 1 hour)?
            $limit = $petition->limit;

            $time_limit = (isset($petition->time) ? $petition->time : 3600); // 3600 = 60 * 60

            $limitModel = new \Daycry\RestServer\Models\LimitModel();
            //$limitModel->setTableName( $this->_restConfig->restLimitsTable );

            // Get data about a keys' usage and limit to one row
            $result = $limitModel->where('uri', $limited_uri)->where('api_key', $api_key)->first();

            // No calls have been made for this key
            if ($result === null) {
                $data = [
                    'uri'          => $limited_uri,
                    'api_key'      => $api_key,
                    'count'        => 1,
                    'hour_started' => time(),
                ];

                $limitModel->save($data);
            }

            // Been a time limit (or by default an hour) since they called
            elseif ($result->hour_started < (time() - $time_limit)) {
                $result->hour_started = time();
                $result->count = 1;

                // Reset the started period and count
                $limitModel->save($result);
            }

            // They have called within the hour, so lets update
            else {
                // The limit has been exceeded
                if ($result->count >= $limit) {
                    return false;
                }

                // Increase the count by one
                $result->count = $result->count + 1;
                $limitModel->save($result);
            }
        }

        return true;
    }
}
