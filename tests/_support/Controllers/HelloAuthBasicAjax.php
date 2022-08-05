<?php

namespace Tests\Support\Controllers;

use Daycry\RestServer\RestServer;

class HelloAuthBasicAjax extends RestServer
{
    public function __construct()
    {
        $this->_restConfig = config('RestServer');
        $this->_restConfig->restAjaxOnly = true;
    }

    public function index()
    {
        $content = array_merge((array)$this->content, array( 'auth' => $this->user ), (array)$this->apiUser);
        return $this->respond($content);
    }
}
