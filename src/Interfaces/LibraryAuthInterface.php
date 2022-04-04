<?php

namespace Daycry\RestServer\Interfaces;

use CodeIgniter\Config\BaseConfig;

interface LibraryAuthInterface
{
    public function __construct(BaseConfig $config = null);
    public function validate($username, $password = true);
}
