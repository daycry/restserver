<?php

namespace Daycry\RestServer\Validators;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\Router\Router;

class Override
{
    public static function check(RequestInterface $request, Router $router)
    {
        $namespaceModel = new \Daycry\RestServer\Models\NamespaceModel();

        $namespaces = $namespaceModel->where('controller', $router->controllerName())->first();

        $requests = ($namespaces->{config('RestServer')->configRestPetitionsTable}) ? $namespaces->{config('RestServer')->configRestPetitionsTable} : [];

        if( !$requests )
        {
            return false;
        }

        $response = null;
        
        foreach( $requests as $r )
        {
            if( \strtolower($r->method) == \strtolower($router->methodName()) && \strtolower($r->http) == \strtolower($request->getMethod()))
            {
                $response = $r;
                break;
            }

            if( \strtolower($r->method) == \strtolower($router->methodName()) && $r->http == null)
            {
                $response = $r;
                break;
            }

            if( \strtolower($r->method) == null && \strtolower($r->http) == \strtolower($request->getMethod()))
            {
                $response = $r;
                break;
            }

            if( $r->method == null && $r->http == null)
            {
                $response = $r;
                break;
            }
        }

        return $response;
    }
}
