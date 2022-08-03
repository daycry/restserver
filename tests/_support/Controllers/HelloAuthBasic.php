<?php

namespace Tests\Support\Controllers;

use Daycry\RestServer\RestServer;

class HelloAuthBasic extends RestServer
{
    public function index()
    {
        $content = array_merge((array)$this->content, array( 'auth' => $this->user ), (array)$this->apiUser);
        return $this->respond($content);
    }

    public function validateParams()
    {
        $this->validation('basicRule', config(\Tests\Support\Config\CustomValidation::class), false, true);

        $content = array_merge((array)$this->content, array( 'auth' => $this->user, 'format' => $this->getOutputFormat() ), (array)$this->apiUser);
        return $this->respond($content);
    }
}