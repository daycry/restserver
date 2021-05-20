<?php
namespace Daycry\RestServer\Libraries\Auth;

interface AuthInterface
{
    public function __construct();
    public function validate();
}