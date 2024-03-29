<?php

namespace Daycry\RestServer;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Config\BaseConfig;
use Psr\Log\LoggerInterface;

use Daycry\RestServer\Exceptions\UnauthorizedException;
use Daycry\RestServer\Exceptions\ValidationException;
use Daycry\RestServer\Exceptions\ForbiddenException;
use Daycry\RestServer\Exceptions\FailTooManyRequestsException;

class RestServer extends ResourceController
{
    /**
     * Router
     */
    protected $router = null;

    /**
     * Doctrine Instance
     */
    protected $doctrine = null;

    /**
     * Encryption Instance
     *
     * @var Daycry\Encryption\Encryption
     */
    protected \Daycry\Encryption\Encryption $encryption;

    /**
     * Response format
     *
     * @var object
     */
    protected string $responseFormat;

    /**
     * Input format
     *
     * @var object
     */
    protected string $inputFormat;

    /**
     * Config of rest server.
     *
     * @var object
     */
    protected BaseConfig $_restConfig;

    /**
     * The authorization log
     *
     * @var string
     */
    protected bool $_isLogAuthorized = false;

    /**
     * Authorized Petition.
     *
     * @var object
     */
    protected bool $authorized = true;

    /**
     * Timer
     */
    private $_benchmark = null;

    /**
     * Petition request
     *
     * @var object
     */
    private ?object $_petition = null;

    /**
     * The arguments from GET, POST, PUT, DELETE, PATCH, HEAD and OPTIONS request methods combined.
     *
     * @var array
     */
    protected array $args = [];

    /**
     * The arguments for the query parameters.
     *
     * @var array
     */
    private array $_queryArgs = [];

    /**
     * The arguments for the query parameters.
     *
     * @var array
     */
    private array $_postArgs = [];

    /**
     * The arguments for the query parameters.
     *
     * @var array
     */
    private array $_headArgs = [];

    /**
     * Information about the current API user.
     *
     * @var object
     */
    protected ?object $apiUser = null;

    /**
     * Information about the current API user.
     *
     * @var string
     */
    protected ?string $key = null;

    /**
     * Information about the current AUTH user.
     *
     * @var object
     */
    protected $user = null;

    /**
     * Extend this function to apply additional checking early on in the process.
     *
     * @return void
     */
    protected function earlyChecks()
    {
    }

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        helper('security');

        parent::initController($request, $response, $logger);

        $this->encryption =  new \Daycry\Encryption\Encryption();
        $this->router = service('router');

        if (class_exists('\Daycry\Doctrine\Doctrine')) {
            // @codeCoverageIgnoreStart
            $this->doctrine = \Config\Services::doctrine();
            // @codeCoverageIgnoreEnd
        }

        // Rest server config
        if (!isset($this->_restConfig)) {
            $this->_restConfig = config('RestServer');
        }

        $this->lang = $this->request->getLocale();

        //set override Petition
        if ($this->_restConfig->restEnableOverridePetition == true) {
            $this->_petition = \Daycry\RestServer\Validators\Override::check($this->request, $this->router);
        }

        // Log the loading time to the log table
        if (
            (is_null($this->_petition) && $this->_restConfig->restEnableLogging == true) ||
            ($this->_restConfig->restEnableLogging == true && (!is_null($this->_petition) && is_null($this->_petition->log))) ||
            (!is_null($this->_petition) && $this->_petition->log)
        ) {
            $this->_isLogAuthorized = true;
            $this->_benchmark = \Config\Services::timer();
            $this->_benchmark->start('petition');
        }

        // Set up the query parameters
        $this->_queryArgs = $this->request->getGet();
        \parse_str($this->request->uri->getQuery(), $args);
        $this->_queryArgs = array_merge($this->_queryArgs, $args);
        $this->_queryArgs = array_merge($this->_queryArgs, $this->request->uri->getSegments());

        $this->_postArgs = $this->request->getPost();

        //get header vars
        $this->_headArgs = array_map(
            function ($header) {
                return $header->getValueLine();
            },
            $this->request->headers()
        );

        $this->args = array_merge($this->_queryArgs, $this->_headArgs, $this->_postArgs);

        // Try to find a format for the request (means we have a request body)
        $this->inputFormat = \Daycry\RestServer\Formats\Input::check($this->request);
        $ft = explode('/', $this->inputFormat);
        //$this->setFormat(end($ft));

        // Try to find a format for the response
        $this->responseFormat = \Daycry\RestServer\Formats\Output::check($this->request, $this->args);
        $ft = explode('/', $this->responseFormat);
        $this->setResponseFormat(end($ft));
        $formatter = $this->format(); //call this function for force output format

        // Extend this function to apply additional checking early on in the process
        $this->earlyChecks();
    }

    /**
     * Check if there is a specific auth type set for the current class/method/HTTP-method being called.
     *
     * @return bool
     */
    private function _authOverrideCheck()
    {
        if (!$this->_petition || ($this->_petition && !$this->_petition->auth)) {
            return false;
        }

        $this->user = $this->_getAuthMethod(\strtolower($this->_petition->auth));

        return true;
    }

    /**
     * Get a auth method
     */
    private function _getAuthMethod(string $method)
    {
        $classMap = $this->_restConfig->restAuthClassMap;
        if ($method && isset($classMap[ \strtolower($method) ])) {
            $method = \strtolower($method);
            $this->authMethodclass = new $classMap[ $method ]();

            if (\is_callable([ $this->authMethodclass, 'validate' ])) {
                return $this->authMethodclass->validate();
            }
        }

        return null;
    }

    protected function validation(String $rules, \Config\Validation $config = null, bool $getShared = true, bool $filter = false)
    {
        $this->validator = \Config\Services::validation($config, $getShared);

        $content = json_decode(json_encode($this->content), true);
        if (!$this->validator->run($content, $rules)) {
            throw ValidationException::validationError();
        }
        unset($content);

        if ($filter) {
            if ($this->content) {
                foreach ($this->content as $key => $value) {
                    if (!array_key_exists($key, $config->{$rules})) {
                        throw ForbiddenException::validationtMethodParamsError($key);
                    }
                }
            }
        }
    }

    /**
     * Add the request to the log table.
     *
     * @param bool $authorized TRUE the user is authorized; otherwise, FALSE
     *
     * @return bool TRUE the data was inserted; otherwise, FALSE
     */
    protected function _logRequest($authorized = false)
    {
        // Insert the request into the log table
        $logModel = new \Daycry\RestServer\Models\LogModel();

        $params = $this->args ? ($this->_restConfig->restLogsJsonParams == true ? \json_encode($this->args) : \serialize($this->args)) : null;
        $params = ($params != null && $this->_restConfig->restEncryptLogParams == true) ? $this->encryption->encrypt($params) : $params;

        $data = [
            'uri'        => $this->request->uri,
            'method'     => $this->request->getMethod(),
            'params'     => $params,
            'api_key'    => isset($this->key) ? $this->key : '',
            'ip_address' => $this->request->getIPAddress(),
            'duration'   => $this->_benchmark->getElapsedTime('petition'),
            'response_code' => $this->response->getStatusCode(),
            'authorized' => $authorized,
        ];

        $logModel->save($data);
        $this->_logId = $logModel->getInsertID();
    }

    protected function getOutputFormat()
    {
        $ft = explode('/', $this->responseFormat);
        return end($ft);
    }

    /**
     * Requests are not made to methods directly, the request will be for
     * an "object". This simply maps the object and method to the correct
     * Controller method.
     *
     * @param string $object_called
     * @param array  $arguments     The arguments passed to the controller method
     *
     * @throws Exception
     */
    public function _remap($method, ...$params)
    {
        $parser = \Config\Services::parser();

        try {
            if (config('App')->forceGlobalSecureRequests && $this->request->isSecure() === false) {
                // @codeCoverageIgnoreStart
                throw ForbiddenException::forUnsupportedProtocol();
                // @codeCoverageIgnoreEnd
            }

            if ($this->request->isAJAX() === false && $this->_restConfig->restAjaxOnly) {
                throw ForbiddenException::forOnlyAjax();
            }

            if ($this->_restConfig->checkCors == true) {
                \Daycry\RestServer\Validators\Cors::check($this->request, $this->router, $this->response);
            }

            if ($this->_restConfig->restEnableInvalidAttempts == true) {
                $attemp = \Daycry\RestServer\Validators\Attemp::check($this->request);
                if ($attemp !== true) {
                    throw FailTooManyRequestsException::forInvalidAttemptsLimit($this->request->getIPAddress(), $attemp);
                }
            }

            if ($this->_restConfig->restIpBlacklistEnabled == true) {
                if (!\Daycry\RestServer\Validators\BlackList::check($this->request)) {
                    throw UnauthorizedException::forIpDenied();
                }
            }

            if ($this->_restConfig->restIpWhitelistEnabled == true) {
                if (!\Daycry\RestServer\Validators\WhiteList::check($this->request)) {
                    throw UnauthorizedException::forIpDenied();
                }
            }

            $this->apiUser = \Daycry\RestServer\Validators\ApiKey::check($this->request, $this->args, $this->_petition, $this->key);

            if ($this->_restConfig->strictApiAndAuth && $this->apiUser instanceof \Exception) {
                throw $this->apiUser;
            }

            if (!$this->_authOverrideCheck()) {
                if (!$this->_restConfig->restEnableKeys || $this->_restConfig->allowAuthAndKeys) {
                    $this->user = $this->_getAuthMethod($this->_restConfig->restAuth);
                }

                if ($this->_restConfig->allowAuthAndKeys && !$this->user && $this->apiUser instanceof \Exception) {
                    throw UnauthorizedException::forUnauthorized();
                }

                if ($this->_restConfig->restEnableKeys && !$this->_restConfig->allowAuthAndKeys && $this->apiUser instanceof \Exception) {
                    throw $this->apiUser;
                }

                if ($this->apiUser instanceof \Exception) {
                    $this->apiUser = null;
                }
            }

            // Check to see if this key has access to the requested controller
            if ($this->_restConfig->restEnableKeys && empty($this->apiUser) === false && \Daycry\RestServer\Validators\Access::check($this->request, $this->router, $this->apiUser) === false) {
                throw UnauthorizedException::forApiKeyUnauthorized();
            }

            // Doing key related stuff? Can only do it if they have a key right?
            if ($this->_restConfig->restEnableKeys && empty($this->apiUser) === false) {
                // Check the limit
                if ($this->_restConfig->restEnableLimits && \Daycry\RestServer\Validators\Limit::check($this->request, $this->router, $this->apiUser, $this->_petition) === false) {
                    throw FailTooManyRequestsException::forApiKeyLimit($this->apiUser->key);
                }

                // If no level is set use 0, they probably aren't using permissions
                $level = ($this->_petition && !empty($this->_petition->level)) ? $this->_petition->level : 0;

                // If no level is set, or it is lower than/equal to the key's level
                if ($level > $this->apiUser->level) {
                    // They don't have good enough perms
                    throw UnauthorizedException::forApiKeyPermissions();
                }
            }
            //check request limit by ip without login
            elseif ($this->_restConfig->restLimitsMethod == 'IP_ADDRESS' && $this->_restConfig->restEnableLimits && \Daycry\RestServer\Validators\Limit::check($this->request, $this->router, $this->apiUser, $this->_petition) === false) {
                throw FailTooManyRequestsException::forIpAddressTimeLimit();
            }

            if ($this->inputFormat == 'application/json') {
                $this->content = $this->request->getJSON();
            } else {
                // @codeCoverageIgnoreStart
                $this->content = $this->request->getRawInput();
                // @codeCoverageIgnoreEnd
            }

            $this->args = array_merge($this->args, (array)$this->content);

            if (!method_exists($this, $this->router->methodName())) {
                throw ForbiddenException::forInvalidMethod($this->router->methodName());
            }

            return \call_user_func_array([ $this, $this->router->methodName() ], $params);
        } catch (\Exception $ex) {
            if (property_exists($ex, 'authorized')) {
                $this->authorized = $ex::$authorized;
            }

            $message = (isset($this->validator) && $this->validator->getErrors()) ? $this->validator->getErrors() : $ex->getMessage();

            if ($ex->getCode()) {
                return $this->fail($message, $ex->getCode());
            } else {
                return $this->fail($message);
            }
        }
    }

    public function getVar($index = null, $filter = null, $flags = null)
    {
        return $this->request->getVar($index, $filter, $flags);
    }

    /**
     * De-constructor.
     *
     * @return void
     */
    public function __destruct()
    {
        if ($this->request && $this->_restConfig) {
            // Log the loading time to the log table
            if ($this->_isLogAuthorized === true) {
                $this->_benchmark->stop('petition');
                $this->_logRequest($this->authorized);
            }

            if ($this->_restConfig->restEnableInvalidAttempts == true) {
                $attemptModel = new \Daycry\RestServer\Models\AttemptModel();
                $attempt = $attemptModel->where('ip_address', $this->request->getIPAddress())->first();

                if ($this->authorized === false) {
                    if ($attempt === null) {
                        $attempt = [
                                'ip_address' => $this->request->getIPAddress(),
                                'attempts'      => 1,
                                'hour_started' => time(),
                            ];

                        $attemptModel->save($attempt);
                    } else {
                        if ($attempt->attempts < $this->_restConfig->restMaxAttempts) {
                            $attempt->attempts = $attempt->attempts + 1;
                            $attempt->hour_started = time();
                            $attemptModel->save($attempt);
                        }
                    }
                } else {
                    if ($attempt) {
                        $attemptModel->delete($attempt->id, true);
                    }
                }
            }
        }

        //reset previous validation at end
        if ($this->validator) {
            $this->validator->reset();
        }
    }
}
