<?php

namespace Daycry\RestServer\Interfaces;

use CodeIgniter\Config\BaseConfig;

interface LibraryAuthInterface
{
    public function validate($username, $password = true);
}
