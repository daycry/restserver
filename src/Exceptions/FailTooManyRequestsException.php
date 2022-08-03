<?php

namespace Daycry\RestServer\Exceptions;

class FailTooManyRequestsException extends \RuntimeException implements \Daycry\RestServer\Interfaces\FailTooManyRequestsInterface
{
    protected $code = 429;

    public static $authorized = true;

    public static function forApiKeyLimit(string $key)
    {
        self::$authorized = false;
        $parser = \Config\Services::parser();
        return new self($parser->setData(array( 'key' => $key ))->renderString(lang('Rest.textRestApiKeyTimeLimit')));
    }

    public static function forInvalidAttemptsLimit(string $ip, string $date)
    {
        self::$authorized = false;
        $parser = \Config\Services::parser();
        return new self($parser->setData(array( 'ip' => $ip, 'date' => $date ))->renderString(lang('Rest.textRestInvalidAttemptsLimit')));
    }

    public static function forIpAddressTimeLimit()
    {
        self::$authorized = false;
        return new self(lang('Rest.textRestIpAddressTimeLimit'));
    }
}
