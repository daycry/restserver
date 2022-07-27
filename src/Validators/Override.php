<?php

namespace Daycry\RestServer\Validators;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\Router\Router;

class Override
{
    public static function check(RequestInterface $request, Router $router)
    {
        $petitionModel = new \Daycry\RestServer\Models\PetitionModel();

        $petition = $petitionModel->where('controller', $router->controllerName())->where('method', $router->methodName())->where('http', $request->getMethod())->first();

        if (!$petition) {
            $petition = $petitionModel->where('controller', $router->controllerName())->where('method', $router->methodName())->where('http', '*')->first();
            if (!$petition) {
                $petition = $petitionModel->where('controller', $router->controllerName())->where('method', null)->where('http', $request->getMethod())->first();
                if (!$petition) {
                    $petition = $petitionModel->where('controller', $router->controllerName())->where('method', null)->where('http', null)->first();
                }
            }
        }

        return $petition;
    }
}
