<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Support\Controllers;

use Daycry\RestServer\RestServer;

class Hello extends RestServer
{
    public function index()
    {
        return $this->respond($this->content);
    }
}