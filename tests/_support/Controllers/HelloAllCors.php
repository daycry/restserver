<?php

namespace Tests\Support\Controllers;

use Daycry\RestServer\RestServer;

class HelloAllCors extends RestServer
{
    public function __construct()
    {
        $this->_restConfig = config('RestServer');
        $this->_restConfig->allowAnyCorsDomain = true;
    }

    public function index()
    {
        return $this->respond($this->content);
    }
}