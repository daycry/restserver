<?php

namespace Tests\Support\Controllers;

use Daycry\RestServer\RestServer;

class NoAccess extends RestServer
{
    public function index()
    {
        return $this->respond($this->content);
    }
}