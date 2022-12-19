<?php

namespace Daycry\RestServer\Libraries\Auth;

use Daycry\RestServer\Interfaces\AuthInterface;
use Daycry\RestServer\Exceptions\UnauthorizedException;

class WhiteListAuth extends BaseAuth implements AuthInterface
{
    public function __construct()
    {
        $this->method = 'whitelist';
        parent::__construct();
    }

    public function validate()
    {
        if (!\Daycry\RestServer\Validators\WhiteList::check($this->request)) {
            // @codeCoverageIgnoreStart
            throw UnauthorizedException::forIpDenied();
            // @codeCoverageIgnoreEnd
        }

        return $this->request->getIPAddress();
    }
}
