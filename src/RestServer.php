<?php namespace Daycry\RestServer;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

use Config\Database;

use Daycry\RestServer\Exceptions\UnauthorizedException;
use Daycry\RestServer\Exceptions\UnauthorizedInterface;

use Daycry\RestServer\Exceptions\ValidationException;
use Daycry\RestServer\Exceptions\ForbiddenException;

class RestServer extends ResourceController
{
    /**
     * Request of petition
     */
    protected $request = null;

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
	 */
    protected $encryption = null;

    /**
     * DBGroup
     */
    protected $db = null;
    
    /**
	 * Validation
	 */
    protected $validator = null;

    /**
	 * Language
	 */
    protected $lang = null;

    /**
     * Information about the current API user.
     *
     * @var object
     */
    protected $apiUser = null;

    /**
     * Key value
     */
    protected $key = null;

    /**
     * Information about the current API user.
     *
     * @var object
     */
    protected $user = false;

    /**
     * Auth method
     * 
     * @var class
     */
    private $authMethodclass = null;

    /**
     * The arguments for the query parameters.
     *
     * @var array
     */
    private $_queryArgs = [];

    /**
     * The arguments for the query parameters.
     *
     * @var array
     */
    private $_postArgs = [];

    /**
     * The arguments for the HEAD.
     *
     * @var array
     */
    private $_headArgs = [];

    /**
     * The arguments from GET, POST, PUT, DELETE, PATCH, HEAD and OPTIONS request methods combined.
     *
     * @var array
     */
    protected $args = [];

    /**
     * The authorization log
     *
     * @var string
     */
    protected $_isLogAuthorized = false;

    /**
     * Timer
     */
    private $_benchmark = null;

    /**
     * Config of rest server.
     *
     * @var object
     */
    private $_restConfig = null;

    /**
     * List all supported methods, the first will be the default format.
     *
     * @var array
     */
    private $_supported_formats = null;

    /**
     * Response format
     *
     * @var object
     */
    protected $responseFormat;

    /**
     * is SSL request
     *
     * @var array
     */
    private $_ssl = false;

    /**
     * If the request is allowed based on the IP provided.
     *
     * @var bool
     */
    private $_ipAllow = true;

    /**
     * If the request is allowed based on the API key provided.
     *
     * @var bool
     */
    private $_allow = true;

    /**
     * Petition request
     *
     * @var object
     */
    private $_petition = null;

    /**
     * @var bool
     */
    private $_authOverride = false;

    /**
     * Extend this function to apply additional checking early on in the process.
     *
     * @return void
     */
    protected function early_checks()
    {
    }

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
	{
        helper( 'security' );
        helper( 'setting' );

        parent::initController( $request, $response, $logger );

        $this->encryption =  new \Daycry\Encryption\Encryption();
        $this->request = $request;
        $this->router = service('router');
        $this->responseFormat = new \stdClass();

        $this->db = Database::connect( setting( 'Daycry\\RestServer\\RestServer.restDatabaseGroup' ) );

        if( class_exists( '\\Daycry\\Doctrine\\Doctrine' ) )
        {
            $this->doctrine = \Config\Services::doctrine();
        }

        // Rest server config
        $this->_restConfig = config( 'RestServer' );

        //Get lang
        $this->lang = $request->getLocale();

        // Check to see if the current IP address is blacklisted
        if( $this->_restConfig->restIpBlacklistEnabled === true )
        {
            $this->_ipAllow = $this->_checkBlacklistAuth();
            if( !$this->_ipAllow )
            {
                return false;
                //throw UnauthorizedException::forIpDenied();
            }
        }

        if( $this->_restConfig->restIpWhitelistEnabled === true )
        {
            $this->_ipAllow = $this->_checkWhitelistAuth();
            if( !$this->_ipAllow )
            {
                return false;
                //throw UnauthorizedException::forIpDenied();
            }
        }
        
        //set override Petition
        if( $this->_restConfig->restEnableOverridePetition === true )
        {
            $this->_petition = $this->_getPetition();
        }

        // Log the loading time to the log table
        if( 
            ( is_null( $this->_petition ) && $this->_restConfig->restEnableLogging === true ) ||
            ( $this->_restConfig->restEnableLogging === true && ( !is_null( $this->_petition ) && is_null( $this->_petition->log ) ) ) ||
            ( !is_null( $this->_petition ) && $this->_petition->log ) 
        )
        {
            $this->_isLogAuthorized = true;
            $this->_benchmark = \Config\Services::timer();
            $this->_benchmark->start( 'petition' );
        }

        // Response Format - If no Header Accept get default format
        $this->_supported_formats = config( 'Format' )->supportedResponseFormats;
        $ft = $request->negotiate( 'media', $this->_supported_formats );
        $this->setResponseFormat( $ft );
        $formatter = $this->format(); //call this function for force output format

        $f = explode( "/", $ft );
        if( isset( $f[ 1 ] ) )
        {
            $this->responseFormat->format = $f[ 1 ];
            $this->responseFormat->mime = $ft;
        }

        // Try to find a format for the request (means we have a request body)
        $inputFormat = $this->_detectInputFormat();

        // Determine whether the connection is HTTPS
        $this->_ssl = $request->isSecure();

        // Check for CORS access request
        $checkCors = $this->_restConfig->checkCors;
        if( $checkCors === TRUE )
        {
            $this->_checkCors();
        }

        // Set up the query parameters
        $this->_parseQuery();
        $this->_parsePost();
        $this->_queryArgs = array_merge( $this->_queryArgs, $this->_detectSegment() );

        //get header vars
        $this->_headArgs = $this->_getHeaders();

        $this->args = array_merge( $this->_queryArgs, $this->_headArgs, $this->_postArgs );
            
        
        // Extend this function to apply additional checking early on in the process
        $this->early_checks();

        $usekey = $this->_restConfig->restEnableKeys;

        // Check if there is a specific auth type for the current class/method
        if( $this->_petition )
        {
            $this->_authOverride = $this->_authOverrideCheck();
            
            if( isset( $this->_petition->key ) )
            {
                $usekey = ( $this->_petition->key === null || $this->_petition->key == 1 ) ? $this->_restConfig->restEnableKeys : false;
            }
        }

        // Checking for keys? GET TO WorK!
        //if( $this->_restConfig->restEnableKeys && $this->_authOverride !== true )
        if( $usekey )
        {
            $this->_allow = $this->_detectApiKey();
        }

        // When there is no specific override for the current class/method, use the default auth value set in the config
        if( $this->_authOverride === false && ( !( $this->_restConfig->restEnableKeys && $this->_allow === true ) || ( $this->_restConfig->allowAuthAndKeys === true && $this->_allow === true ) ) )
        {
            $this->user = $this->_getAuthMethod( $this->_restConfig->restAuth );

            if( $this->_restConfig->restIpWhitelistEnabled === true )
            {
                $this->_checkWhitelistAuth();
            }
        }

        if( $inputFormat == 'application/json' )
        {
            $this->content = $request->getJSON();
        }else{
            $this->content = (object)$request->getRawInput();
        }
    }

    /**
     * Get petition of request
     * 
     * @return string|null Supported input format; otherwise, NULL
     * @access private
     */
    private function _getPetition()
    {
        //set Operation
        $petitionModel = new \Daycry\RestServer\Models\PetitionModel( $this->db );
        $petitionModel->setTableName( $this->_restConfig->configRestPetitionsTable );
        
        $petition = $petitionModel->where( 'controller', $this->router->controllerName() )->where( 'method', $this->router->methodName() )->where( 'http', $this->request->getMethod() )->first();

        if( !$petition )
        {
            $petition = $petitionModel->where( 'controller', $this->router->controllerName() )->where( 'method', $this->router->methodName() )->where( 'http', '*' )->first();
            if( !$petition )
            {
                $petition = $petitionModel->where( 'controller', $this->router->controllerName() )->where( 'method', null )->where( 'http', $this->request->getMethod() )->first();
                if( !$petition )
                {
                    $petition = $petitionModel->where( 'controller', $this->router->controllerName() )->where( 'method', null )->where( 'http', null )->first();
                }
            }
        }

        return $petition;
    }

    /**
     * Parse the query parameters.
     *
     * @return void
     */
    protected function _parseQuery()
    {
        $this->_queryArgs = $this->request->getGet();
    }

    /**
     * Parse the query parameters.
     *
     * @return void
     */
    protected function _parsePost()
    {
        $this->_postArgs = $this->request->getPost();
    }

    /**
     *Parse Headers
     */
    protected function _getHeaders()
    {
        $headers = array_map(
            function( $header )
            { 
                return $header->getValueLine(); 
            }, 
            $this->request->getHeaders()
        );

        return $headers;
    }

    /**
     * Detect de query segment
     *
     * @return array
     */
    private function _detectSegment()
    {
        $i = 0;
		$lastval = '';
        $retval = array();

		foreach( $this->request->uri->getSegments() as $seg )
		{
			if ( $i % 2 )
			{
				$retval[ $lastval ] = $seg;
			}
			else
			{
				$retval[ $seg ] = NULL;
				$lastval = $seg;
			}

			$i++;
        }

        return $retval;
    }

    /**
     * Get the input format e.g. json or xml.
     *
     * @access private
     * @return string|null Supported input format; otherwise, NULL
     */
    private function _detectInputFormat()
    {
        // Get the CONTENT-TYPE value from the SERVER variable
        //$content_type = $this->request->getServer( 'CONTENT_TYPE' );
        $content_type = $this->request->getHeaderLine( 'CONTENT-TYPE' );

        if( empty( $content_type ) === false )
        {
            foreach( $this->_supported_formats as $type )
            {
                // $type = mime type e.g. application/json
                if( $content_type === $type )
                {
                    $ft = explode( '/', $content_type );
                    $this->setFormat( end( $ft ) );
                    return $type;
                }
            }
        }
    }

    /**
     * Check if there is a specific auth type set for the current class/method/HTTP-method being called.
     *
     * @return bool
     */
    private function _authOverrideCheck()
    {
        if( !$this->_petition ){ return false; }
        if( !$this->_petition->auth ){ return false; }

        $this->user = $this->_getAuthMethod( \strtolower( $this->_petition->auth ) );

        return true;
    }

    /**
     * Get a auth method
     */
    private function _getAuthMethod( String $method )
    {
        $classMap = $this->_restConfig->restAuthClassMap;
        if( $method && isset( $classMap[ \strtolower( $method ) ] ) )
        {
            $this->authMethodclass = new $classMap[ \strtolower( $method ) ]();

            if( \is_callable( [ $this->authMethodclass, 'validate' ] ) )
            {
                try
                {
                    return $this->authMethodclass->validate();
                }catch( \Exception $ex )
                {
                    log_message( 'critical', $ex->getMessage() );
                    return $ex;
                }
            }
        }

        return true;
    }

    /**
     * Checks if the client's ip is in the 'restIpBlacklist' config and generates a 401 response
     *
     * @access private
     * @return void
     */
    private function _checkBlacklistAuth()
    {
        // Match an ip address in a blacklist e.g. 127.0.0.0, 0.0.0.0
        $pattern = sprintf( '/(?:,\s*|^)\Q%s\E(?=,\s*|$)/m', $this->request->getIPAddress() );

        // Returns 1, 0 or FALSE (on error only). Therefore implicitly convert 1 to TRUE
        if ( preg_match( $pattern, $this->_restConfig->restIpBlacklist ) )
        {
            return false;
        }

        return true;
    }

    /**
     * Check if the client's ip is in the 'restIpWhitelist' config and generates a 401 response
     *
     * @access protected
     * @return void
     */
    private function _checkWhitelistAuth()
    {
        $whitelist = explode( ',', $this->_restConfig->restIpWhitelist );

        array_push( $whitelist, '127.0.0.1', '0.0.0.0' );

        foreach( $whitelist as &$ip )
        {
            // As $ip is a reference, trim leading and trailing whitespace, then store the new value
            // using the reference
            $ip = trim( $ip );
        }

        if( in_array( $this->request->getIPAddress(), $whitelist ) === false )
        {
            return false;
        }

        return true;
    }

    /**
     * See if the user has provided an API key.
     *
     * @return bool
     */
    protected function _detectApiKey()
    {
        // Get the api key name variable set in the rest config file
        $api_key_variable = $this->_restConfig->restKeyName;

        // Work out the name of the SERVER entry based on config
        //$key_name = 'HTTP_' . strtoupper( str_replace( '-', '_', $api_key_variable ) );

        // Find the key from server or arguments
        if( ( $this->key = isset( $this->args[ $api_key_variable ] ) ? $this->args[ $api_key_variable ] : $this->request->getHeaderLine( $api_key_variable ) ) )
        {
            $keyModel = new \Daycry\RestServer\Models\KeyModel( $this->db );
            $keyModel->setTableName( $this->_restConfig->restKeysTable );
            $keyModel->setKeyName( $this->_restConfig->restKeyColumn );

            if( !( $row = $keyModel->where( $this->_restConfig->restKeyColumn, $this->key )->first() ) )
            {
                return false;
            }

            $this->key = $row->{ $this->_restConfig->restKeyColumn };

            $this->apiUser = $row;

            /*
             * If "is private key" is enabled, compare the ip address with the list
             * of valid ip addresses stored in the database
             */
            if( empty( $row->is_private_key ) === false )
            {
                $found_address = false;
                // Check for a list of valid ip addresses
                if( isset( $row->ip_addresses ) )
                {
                    $ip_address = $this->request->getIPAddress();
                    
                    // multiple ip addresses must be separated using a comma, explode and loop
                    $list_ip_addresses = explode( ',', $row->ip_addresses );
                    
                    foreach( $list_ip_addresses as $list_ip )
                    {
                        if( strpos( $list_ip, '/' ) !== false )
                        {
                            //check IP is in the range
                            $found_address = \Daycry\RestServer\Libraries\CheckIp::ipv4_in_range( trim( $list_ip ), $row->ip_addresses );
                        }
                        else if( $ip_address === trim( $list_ip ) )
                        {
                            // there is a match, set the the value to TRUE and break out of the loop
                            $found_address = true;
                            break;
                        }
                    }

                    return $found_address;

                } else {
                    // There should be at least one IP address for this private key
                    return false;
                }
            }

            return true;
        }

        // No key has been sent
        return false;
    }

    /**
     * Check if the requests to a controller method exceed a limit.
     *
     * @param string $controller_method The method being called
     *
     * @return bool TRUE the call limit is below the threshold; otherwise, FALSE
     */
    private function _checkLimit()
    {
        if( $this->_petition )
        {
            // They are special, or it might not even have a limit
            if( isset( $this->apiUser ) && isset( $this->apiUser->ignore_limits ) && empty( $this->apiUser->ignore_limits ) === false )
            {
                // Everything is fine
                return true;
            }

            $api_key = isset( $this->key ) ? $this->key : null;

            switch( $this->_restConfig->restLimitsMethod )
            {
                case 'IP_ADDRESS':
                    $api_key = $this->request->getIPAddress();
                    $limited_uri = 'ip-address:' . $api_key;
                    break;

                case 'API_KEY':
                    $limited_uri = 'api-key:' . $api_key;
                    break;

                case 'METHOD_NAME':
                    $limited_uri = 'method-name:' . $this->_petition->controller . '::' . $this->_petition->method;
                    break;

                case 'ROUTED_URL':
                default:
                    $limited_uri = $this->request->getPath();
                    $limited_uri = 'uri:'.$limited_uri.':'.$this->request->getMethod(); // It's good to differentiate GET from PUT
                    break;
            }

            if( $this->_petition->limit === false )
            {
                // Everything is fine
                return true;
            }

            // How many times can you get to this method in a defined time_limit (default: 1 hour)?
            $limit = $this->_petition->limit;

            $time_limit = ( isset( $this->_petition->time ) ? $this->_petition->time : 3600 ); // 3600 = 60 * 60


            $limitModel = new \Daycry\RestServer\Models\LimitModel( $this->db );
            $limitModel->setTableName( $this->_restConfig->restLimitsTable );

            // Get data about a keys' usage and limit to one row
            $result = $limitModel->where( 'uri', $limited_uri )->where( 'api_key', $api_key )->first();

            // No calls have been made for this key
            if( $result === null )
            {
                $data = [
                    'uri'          => $limited_uri,
                    'api_key'      => $api_key,
                    'count'        => 1,
                    'hour_started' => time(),
                ];

                $limitModel->save( $data );
            }

            // Been a time limit (or by default an hour) since they called
            elseif( $result->hour_started < ( time() - $time_limit ) )
            {
                $result->hour_started = time();
                $result->count = 1;

                // Reset the started period and count
                $limitModel->save( $result );
            }

            // They have called within the hour, so lets update
            else {
                // The limit has been exceeded
                if( $result->count >= $limit )
                {
                    return false;
                }

                // Increase the count by one
                $result->count = $result->count + 1;
                $limitModel->save( $result );
            }
        }

        return true;
    }

    /**
     * Check to see if the API key has access to the controller and methods.
     *
     * @return bool TRUE the API key has access; otherwise, FALSE
     */
    protected function _checkAccess()
    {
        // If we don't want to check access, just return TRUE
        if( $this->_restConfig->restEnableAccess === false )
        {
            return true;
        }

        $accessModel = new \Daycry\RestServer\Models\AccessModel( $this->db );
        $accessModel->setTableName( $this->_restConfig->restAccessTable );

        //check if the key has all_access
        $results = $accessModel->where( 'api_key', $this->key )->where( 'controller', $this->router->controllerName() )->findAll();

        $return = false;

        if( !empty( $results ) )
        {
            foreach( $results as $result )
            {
                if( $result->all_access )
                {
                    $return = true;
                    break;
                }else{
                    if( $this->router->methodName() == $result->method )
                    {
                        $return = true;
                        break;
                    }
                }
            }
        }

        return $return;
    }

    /**
     * Checks allowed domains, and adds appropriate headers for HTTP access control (CORS)
     *
     * @access protected
     * @return void
     */
    protected function _checkCors()
    {
        // Convert the config items into strings
        $allowed_headers = implode( ', ', $this->_restConfig->allowedCorsHeaders );
        $allowed_methods = implode( ', ', $this->_restConfig->allowedCorsMethods );

        // If we want to allow any domain to access the API
        if( $this->_restConfig->allowAnyCorsDomain === true )
        {
            header( 'Access-Control-Allow-Origin: *' );
            header( 'Access-Control-Allow-Headers: ' . $allowed_headers );
            header( 'Access-Control-Allow-Methods: ' . $allowed_methods );
        }
        else
        {
            // We're going to allow only certain domains access
            // Store the HTTP Origin header
            $origin = $this->request->getServer( 'HTTP_ORIGIN' );
            if( $origin === NULL )
            {
                $origin = '';
            }

            // If the origin domain is in the allowed_cors_origins list, then add the Access Control headers
            if( in_array( $origin, $this->_restConfig->allowedCorsOrigins ) )
            {
                header( 'Access-Control-Allow-Origin: ' . $origin );
                header( 'Access-Control-Allow-Headers: ' . $allowed_headers );
                header( 'Access-Control-Allow-Methods: ' . $allowed_methods );
            }
        }

        // If there are headers that should be forced in the CORS check, add them now
        if( is_array( $this->_restConfig->forcedCorsHeaders ) )
        {
            foreach( $this->_restConfig->forcedCorsHeaders as $header => $value )
            {
                header( $header.': '.$value );
            }
        }

        // If the request HTTP method is 'OPTIONS', kill the response and send it to the client
        if( $this->request->getMethod() === 'options' )
        {
            exit;
        }
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
    public function _remap( $method, ...$params )
    {
        $parser = \Config\Services::parser();

        // Call the controller method and passed arguments
        try
        {
            if( $this->_restConfig->forceHttps && $this->_ssl === false )
            {
                throw ForbiddenException::forUnsupportedProtocol();
            }

            // They provided a key, but it wasn't valid, so get them out of here
            if( $this->_restConfig->restEnableKeys && $this->_allow === false  )
            {
                throw UnauthorizedException::forInvalidApiKey( $this->key );
            }

            if( $this->authMethodclass && $this->authMethodclass->getIsValidRequest() === false )
            {
                throw UnauthorizedException::forInvalidCredentials();
            }

            if( $this->user instanceof UnauthorizedInterface )
            {
                throw $this->user;
            }

            if( $this->_ipAllow === false )
            {
                throw UnauthorizedException::forIpDenied();
            }

            // Check to see if this key has access to the requested controller
            if( $this->_restConfig->restEnableKeys && empty( $this->key ) === false && $this->_checkAccess() === false )
            {    
                throw UnauthorizedException::forApiKeyUnauthorized();
            }

            // Doing key related stuff? Can only do it if they have a key right?
            if( $this->_restConfig->restEnableKeys && empty( $this->key ) === false )
            {
                // Check the limit
                if( $this->_restConfig->restEnableLimits && $this->_checkLimit() === false )
                {
                    throw UnauthorizedException::forApiKeyLimit();
                }

                // If no level is set use 0, they probably aren't using permissions
                $level = ( $this->_petition && !empty( $this->_petition->level ) ) ? $this->_petition->level : 0;
                
                // If no level is set, or it is lower than/equal to the key's level
                $authorized = $level <= $this->apiUser->level;

                if( $authorized === false )
                {
                    // They don't have good enough perms
                    throw UnauthorizedException::forApiKeyPermissions();
                }
            }
            //check request limit by ip without login
            elseif( $this->_restConfig->restLimitsMethod == 'IP_ADDRESS' && $this->_restConfig->restEnableLimits && $this->_checkLimit() === false )
            {
                throw UnauthorizedException::forIpAddressTimeLimit();
            }

            return \call_user_func_array( [ $this, $this->router->methodName() ], $params );

        } catch ( \Daycry\RestServer\Exceptions\UnauthorizedInterface $ex ) {

            return $this->failUnauthorized( $ex->getMessage() );

        } catch ( \Daycry\RestServer\Exceptions\ForbiddenInterface $ex ) {

            return $this->failForbidden( $ex->getMessage() );

        } catch ( \Daycry\RestServer\Exceptions\ValidationInterface $ex ) {

            return $this->fail( $this->validator->getErrors() );

        } catch ( \Exception $ex ) {

            return $this->fail( $ex->getMessage() );
        }
    }

    protected function validation( String $rules, \Config\Validation $config = null )
    {
        $this->validator =  \Config\Services::validation( $config );

        if( !$this->validator->run( (array)$this->content, $rules ) )
        {
            throw ValidationException::validationError();
        }
    }

    /**
     * Add the request to the log table.
     *
     * @param bool $authorized TRUE the user is authorized; otherwise, FALSE
     *
     * @return bool TRUE the data was inserted; otherwise, FALSE
     */
    protected function _logRequest( $authorized = false )
    {
        // Insert the request into the log table
        $logModel = new \Daycry\RestServer\Models\LogModel( $this->db );
        $logModel->setTableName( $this->_restConfig->configRestLogsTable );

        $params = $this->args ? ( $this->_restConfig->restLogsJsonParams === true ? \json_encode( $this->args ) : \serialize( $this->args ) ) : null;
        $params = ( $params != null && $this->_restConfig->restEncryptLogParams === true ) ? $this->encryption->encrypt( $params ) : $params;

        $data = [
            'uri'        => $this->request->uri,
            'method'     => $this->request->getMethod(),
            'params'     => $params,
            'api_key'    => isset( $this->key ) ? $this->key : '',
            'ip_address' => $this->request->getIPAddress(),
            'duration'   => $this->_benchmark->getElapsedTime( 'petition' ),
            'response_code' => $this->response->getStatusCode(),
            'authorized' => $authorized,
        ];
        $logModel->save( $data );
        $this->_logId = $logModel->getInsertID();
    }

    /**
     * De-constructor.
     * 
     * @return void
     */
    public function __destruct()
    {
        // Log the loading time to the log table
        if( $this->_isLogAuthorized === true )
        {
            $this->_benchmark->stop( 'petition' );
            $authorized = ( $this->authMethodclass && $this->authMethodclass->getIsValidRequest() ) ? $this->authMethodclass->getIsValidRequest() : true;
            $this->_logRequest( $authorized );
        }
    }
}