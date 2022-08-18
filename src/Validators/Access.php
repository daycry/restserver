<?php

namespace Daycry\RestServer\Validators;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\Router\Router;

class Access
{
    use \Daycry\RestServer\Traits\Schema;

    public static function check(RequestInterface $request, Router $router, ?object $apiUser): bool
    {
        $return = true;

        if (config('RestServer')->restEnableAccess == true) {
            $return = false;
            $accessModel = new \Daycry\RestServer\Models\AccessModel();
            $results = $accessModel->setSchema(self::getSchema())->with(config('RestServer')->restKeysTable)->where('api_key', $apiUser->key)->where('controller', $router->controllerName())->findAll();

            if (!empty($results)) {
                foreach ($results as $result) {
                    $result = \Daycry\RestServer\Libraries\Utils::modelAliases($result, config('RestServer')->restKeysTable, 'api_key');
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
