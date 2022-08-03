<?php

namespace Daycry\RestServer\Exceptions;

use CodeIgniter\Exceptions\FrameworkException;

class UnauthorizedException extends \RuntimeException implements \Daycry\RestServer\Interfaces\UnauthorizedInterface
{
    protected $code = 401;

    public static $authorized = true;

    /**
     * @codeCoverageIgnore
     */
    public static function forTokenExpired()
    {
        self::$authorized = false;
        return new self(lang('Rest.tokenExpired'));
    }

    /**
     * @codeCoverageIgnore
     */
    public static function forTokenUnauthorized()
    {
        self::$authorized = false;
        return new self(lang('Rest.tokenUnauthorized'));
    }

    public static function forInvalidApiKey($apiKey)
    {
        self::$authorized = false;
        $parser = \Config\Services::parser();
        return new self($parser->setData(array( 'key' => $apiKey ))->renderString(lang('Rest.textRestInvalidApiKey')));
    }

    public static function forApiKeyUnauthorized()
    {
        self::$authorized = false;
        return new self(lang('Rest.textRestApiKeyUnauthorized'));
    }

    public static function forInvalidCredentials()
    {
        self::$authorized = false;
        return new self(lang('Rest.textRestInvalidCredentials'));
    }

    public static function forUnauthorized()
    {
        self::$authorized = false;
        return new self(lang('Rest.textUnauthorized'));
    }

    public static function forIpDenied()
    {
        self::$authorized = false;
        return new self(lang('Rest.ipDenied'));
    }

    public static function forApiKeyPermissions()
    {
        self::$authorized = false;
        return new self(lang('Rest.textRestApiKeyPermissions'));
    }

    public static function forIpAddressTimeLimit()
    {
        self::$authorized = false;
        return new self(lang('Rest.textRestIpAddressTimeLimit'));
    }
}
