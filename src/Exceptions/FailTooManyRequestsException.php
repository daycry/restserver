<?php

namespace Daycry\RestServer\Exceptions;

class FailTooManyRequestsException extends \RuntimeException implements \Daycry\RestServer\Interfaces\FailTooManyRequestsInterface
{
    protected $code = 429;

    public static function forApiKeyLimit( string $key )
    {
        $parser = \Config\Services::parser();
        return new self($parser->setData(array( 'key' => $key ))->renderString(lang('Rest.textRestApiKeyTimeLimit')));
    }

    public static function forInvalidAttemptsLimit(string $ip, string $date)
    {
        $parser = \Config\Services::parser();
        return new self($parser->setData(array( 'ip' => $ip, 'date' => $date ))->renderString(lang('Rest.textRestInvalidAttemptsLimit')));
    }
}
