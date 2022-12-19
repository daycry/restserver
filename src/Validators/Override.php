<?php

namespace Daycry\RestServer\Validators;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\Router\Router;

use Daycry\RestServer\Entities\PetitionEntity;

class Override
{
    public static function check(RequestInterface $request, Router $router)
    {
        $response = $requests = null;

        $namespaceModel = new \Daycry\RestServer\Models\NamespaceModel();

        $namespace = $namespaceModel->where('controller', $router->controllerName())->first();

        if ($namespace) {
            $requests = ($namespace->{config('RestServer')->configRestPetitionsTable}) ? $namespace->{config('RestServer')->configRestPetitionsTable} : [];
        }


        if (!$requests) {
            return $response;
        }

        foreach ($requests as $r) {
            // @codeCoverageIgnoreStart
            if (!$r instanceof PetitionEntity) {
                $r = new PetitionEntity((array)$r);
            }
            // @codeCoverageIgnoreEnd


            if ($r->method && \strtolower($r->method) == \strtolower($router->methodName()) && $r->http && \strtolower($r->http) == \strtolower($request->getMethod())) {
                $response = $r;
                break;
            }

            if ($r->method && \strtolower($r->method) == \strtolower($router->methodName()) && $r->http == null) {
                $response = $r;
                break;
            }

            if ($r->method && \strtolower($r->method) == null && $r->http && \strtolower($r->http) == \strtolower($request->getMethod())) {
                $response = $r;
                break;
            }

            if ($r->method == null && $r->http == null) {
                $response = $r;
                break;
            }
        }

        return $response;
    }
}
