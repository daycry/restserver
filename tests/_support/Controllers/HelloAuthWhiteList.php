<?php

namespace Tests\Support\Controllers;

use Daycry\RestServer\RestServer;

class HelloAuthWhiteList extends RestServer
{
    public function index()
    {
        $content = array_merge((array)$this->content, array( 'auth' => $this->user ), (array)$this->apiUser);
        return $this->respond($content);
    }
}
