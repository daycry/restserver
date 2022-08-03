<?php

namespace Daycry\RestServer\Exceptions;

class ForbiddenException extends \RuntimeException implements \Daycry\RestServer\Interfaces\ForbiddenInterface
{
    protected $code = 403;

    public static $authorized = true;

    /**
     * @codeCoverageIgnore
     */
    public static function forUnsupportedProtocol()
    {
        self::$authorized = false;
        return new self(lang('Rest.textRestUnsupported'));
    }

    public static function forOnlyAjax()
    {
        self::$authorized = false;
        return new self(lang('Rest.textRestAjaxOnly'));
    }

    public static function validationtMethodParamsError($param)
    {
        self::$authorized = false;
        $parser = \Config\Services::parser();
        return new self($parser->setData(array( 'param' => $param ))->renderString(lang('Rest.textInvalidMethodParams')));
    }

    public static function forInvalidMethod($method)
    {
        self::$authorized = false;
        $parser = \Config\Services::parser();
        return new self($parser->setData(array( 'method' => $method ))->renderString(lang('Rest.textInvalidMethod')));
    }

    public static function forInvalidLibraryImplementation()
    {
        return new self(lang('Rest.textInvalidLibraryImplementation'));
    }
}
