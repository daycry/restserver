<?php

namespace Tests\Support\Controllers;

use Daycry\RestServer\RestServer;

class HelloAuthLibrary extends RestServer
{
    public function __construct()
    {
        $this->_restConfig = config('RestServer');
        /**
         * Override value
         */
        $this->_restConfig->authSource = 'library';
        $this->_restConfig->authLibraryClass['basic'] = \Tests\Support\Libraries\LibraryBasicAuth::class;
    }
    
    public function index()
    {
        $content = array_merge((array)$this->content, array( 'auth' => $this->user ), (array)$this->apiUser);
        return $this->respond($content);
    }
}