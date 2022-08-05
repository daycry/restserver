<?php

namespace Tests\Support\Config;

class CustomValidation extends \Config\Validation
{
    public $basicRule = [
        'param' => 'required',
        'method' => 'required'
    ];
}
