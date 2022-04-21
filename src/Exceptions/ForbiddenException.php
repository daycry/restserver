<?php

namespace Daycry\RestServer\Exceptions;

class ForbiddenException extends \RuntimeException implements \Daycry\RestServer\Interfaces\ForbiddenInterface
{
    protected $code = 403;

    public static function forUnsupportedProtocol()
    {
        return new self(lang('Rest.textRestUnsupported'));
    }

    public static function forOnlyAjax()
    {
        return new self(lang('Rest.textRestAjaxOnly'));
    }

    public static function validationtMethodParamsError($param)
    {
        $parser = \Config\Services::parser();
        return new self($parser->setData(array( 'param' => $param ))->renderString(lang('Rest.textInvalidMethodParams')));
    }

    public static function forInvalidMethod($method)
    {
        $parser = \Config\Services::parser();
        return new self($parser->setData(array( 'method' => $method ))->renderString(lang('Rest.textInvalidMethod')));
    }
}
