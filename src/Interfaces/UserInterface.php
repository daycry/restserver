<?php

namespace Daycry\RestServer\Interfaces;

use CodeIgniter\Validation\ValidationInterface;
use CodeIgniter\Database\ConnectionInterface;

interface UserInterface
{
    public function __construct(?ConnectionInterface &$db = null, ?ValidationInterface $validation = null);
    public function setTableName(string $tableName, string $columnKey);
}
