<?php
namespace Daycry\RestServer\Interfaces;

interface LibraryAuthInterface
{
    public function __construct();
    public function validate( $username, $password = true );
}