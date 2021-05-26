<?php
namespace Daycry\RestServer\Libraries\Auth;

interface LibraryAuthInterface
{
    public function __construct();
    public function validate( $username, $password = true );
}