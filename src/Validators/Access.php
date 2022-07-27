<?php

namespace Daycry\RestServer\Validators;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\Router\Router;

class Access
{
    public static function check(RequestInterface $request, Router $router, ?object $apiUser): bool
    {
        $return = true;

        if (config('RestServer')->restEnableAccess === true) {
            $accessModel = new \Daycry\RestServer\Models\AccessModel();
            $results = $accessModel->where('api_key', $apiUser->key)->where('controller', $router->controllerName())->findAll();

            if (!empty($results)) {
                $return = false;
                foreach ($results as $result) {
                    if ($result->all_access) {
                        $return = true;
                        break;
                    } else {
                        if ($router->methodName() == $result->method) {
                            $return = true;
                            break;
                        }
                    }
                }
            }
        }

        return $return;
    }
}
