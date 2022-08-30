<?php

namespace Daycry\RestServer\Validators;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\Router\Router;

class Access
{
    public static function check(RequestInterface $request, Router $router, ?object $apiUser): bool
    {
        $return = true;

        if (config('RestServer')->restEnableAccess == true) {
            $return = false;
            $accessModel = new \Daycry\RestServer\Models\AccessModel();
            $namespaceModel = new \Daycry\RestServer\Models\NamespaceModel();

            $namespace = $namespaceModel->where('controller', $router->controllerName())->first();

            if (!$namespace) {
                return false;
            }

            $results = $accessModel->where('namespace_id', $namespace->id)->where('key_id', $apiUser->id)->findAll();

            if (!empty($results)) {
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
