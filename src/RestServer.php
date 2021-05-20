<?php namespace Daycry\RestServer;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

use Daycry\RestServer\Exceptions\UnauthorizedException;
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
     * Information about the current API user.
     *
     * @var object
     */
    protected $user = null;

    /**
     * The arguments for the query parameters.
     *
     * @var array
     */
    private $_queryArgs = [];

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
    private $_authOverride;

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
        parent::initController( $request, $response, $logger );

        //$jwtLibrary = new \Daycry\RestServer\Libraries\JWT();
        //$claims = $jwtLibrary->encode( ['holaaa' => 'adioss'] );
        //var_dump( $claims );exit;

        helper( 'security' );

        $this->validator =  \Config\Services::validation();
        $this->encryption =  new \Daycry\Encryption\Encryption();
        $this->request = $request;
        $this->router = service('router');

        if( class_exists( 'Daycry\\Doctrine\\Doctrine' ) )
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
            }
        }

        if( $this->_restConfig->restIpWhitelistEnabled === true )
        {
            $this->_ipAllow = $this->_checkWhitelistAuth();
            if( !$this->_ipAllow )
            {
                return false;
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
        $this->_queryArgs = array_merge( $this->_queryArgs, $this->_detectSegment() );

        //get header vars
        $this->_headArgs = $this->_getHeaders();

        // Extend this function to apply additional checking early on in the process
        $this->early_checks();

        // Check if there is a specific auth type for the current class/method
        if( $this->_petition )
        {
            $this->_authOverride = $this->_authOverrideCheck();
        }

        // Checking for keys? GET TO WorK!
        if( $this->_restConfig->restEnableKeys && $this->_authOverride !== true )
        {
            $this->_allow = $this->_detectApiKey();
        }


        // When there is no specific override for the current class/method, use the default auth value set in the config
        if( $this->_authOverride === false && ( !( $this->_restConfig->restEnableKeys && $this->_allow === true ) || ( $this->_restConfig->allowAuthAndKeys === true && $this->_allow === true ) ) )
        {
            $rest_auth = strtolower( $this->_restConfig->restAuth );
            $this->user = $this->_getAuthMethod( \strtolower( $rest_auth ) );

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

        $this->args = (object)array(
            'query' => $this->_queryArgs,
            'header' => $this->_headArgs,
            'body' => (array)$this->content
        );
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
        $petitionModel = new \Daycry\RestServer\Models\PetitionModel();
        $petitionModel->setTableName( $this->_restConfig->configRestPetitionsTable );
        $petition = $petitionModel->where( 'controller', $this->router->controllerName() )->where( 'method', $this->router->methodName() )->where( 'http', $this->request->getMethod() )->first();

        if( !$petition )
        {
            $petition = $petitionModel->where( 'controller', $this->router->controllerName() )->where( 'method', '*' )->where( 'http', $this->request->getMethod() )->first();

            if( !$petition )
            {
                $petition = $petitionModel->where( 'controller', $this->router->controllerName() )->where( 'method', $this->router->methodName() )->first();

                if( !$petition )
                {
                    $petition = $petitionModel->where( 'controller', $this->router->controllerName() )->where( 'method', '*' )->first();
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
        $content_type = $this->request->getServer( 'CONTENT_TYPE' );

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
    private function _getAuthMethod( $method )
    {
        $classMap = $this->_restConfig->restAuthClassMap;
        if( $method && isset( $classMap[ $method ] ) )
        {
            $class = new $classMap[ $method ]();

            if( \is_callable( [ $class, 'validate' ] ) )
            {
                return $class->validate();
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

        //array_push( $whitelist, '127.0.0.1', '0.0.0.0' );

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
        $key_name = 'HTTP_' . strtoupper( str_replace( '-', '_', $api_key_variable ) );

        //var_dump( $this->_args );
        //exit;

        // Find the key from server or arguments
        if( ( $this->key = isset( $this->_args[ $api_key_variable ] ) ? $this->_args[ $api_key_variable ] : $this->request->getServer( $key_name ) ) )
        {
            $keyModel = new \Daycry\RestServer\Models\KeyModel();
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
    protected function checkRequest( $validation = null )
    {
        $parser = \Config\Services::parser();

        var_dump( $this->_restConfig->forceHttps );
        var_dump( $this->_ssl );
        exit;

        if( $this->_restConfig->forceHttps && $this->_ssl === false )
        {
            throw ForbiddenException::forUnsupportedProtocol();
        }

        // They provided a key, but it wasn't valid, so get them out of here
        if( $this->_restConfig->restEnableKeys && $this->_allow === false  )
        {
            throw UnauthorizedException::forInvalidApiKey( $this->key );
        }

        if( $this->_ipAllow === false )
        {
            throw UnauthorizedException::forIpDenied();
        }

        if( $this->user === false )
        {
            throw UnauthorizedException::forInvalidCredentials();
        }

        if( $validation != null )
        {
            if( !$this->validator->run( (array)$this->content, $validation ) )
            {
                throw ValidationException::validationError();
            }
        }

        return true;
    }
}