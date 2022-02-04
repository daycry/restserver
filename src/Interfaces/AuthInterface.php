<?php
namespace Daycry\RestServer\Interfaces;

interface AuthInterface
{
    public function __construct();
    public function validate();
}