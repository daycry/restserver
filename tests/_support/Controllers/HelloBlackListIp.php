<?php

namespace Tests\Support\Controllers;

use Daycry\RestServer\RestServer;

class HelloBlackListIp extends RestServer
{
    public function __construct()
    {
        $this->_restConfig = config('RestServer');
        $this->_restConfig->restIpBlacklistEnabled = true;
        $this->_restConfig->restIpBlacklist = '0.0.0.0, 127.0.0.1';
    }

    public function index()
    {
        $content = array_merge((array)$this->content, array( 'auth' => $this->user ), (array)$this->apiUser);
        return $this->respond($content);
    }
}