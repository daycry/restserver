<?php

namespace Tests\Support\Controllers;

use Daycry\RestServer\RestServer;

class HelloIpAddressLimitNoApi extends RestServer
{
    public function __construct()
    {
        $this->_restConfig = config('RestServer');
        $this->_restConfig->restEnableKeys = false;
        $this->_restConfig->restLimitsMethod = 'IP_ADDRESS';
    }

    public function index()
    {
        $content = array_merge((array)$this->content, array( 'auth' => $this->user ), (array)$this->apiUser);
        return $this->respond($content);
    }
}
