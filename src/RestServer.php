<?php namespace Daycry\RestServer;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class RestServer extends ResourceController
{
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
     * Config of rest server.
     *
     * @var object
     */
    protected $restConfig = null;

    /**
	 * Language
	 */
    protected $lang = null;

    /**
     * Rest server.
     *
     * @var object
     */
    protected $rest = null;

    /**
     * Input Format
     *
     * @var string
     */
    protected $inputFormat;

    /**
     * The arguments for the GET request method.
     *
     * @var array
     */
    protected $_get_args = [];

    /**
     * The arguments for the body.
     *
     * @var object
     */
    protected $content;

    /**
     * The arguments for the HEAD request method.
     *
     * @var array
     */
    protected $headers = [];

    /**
     * The arguments for the query parameters.
     *
     * @var array
     */
    protected $_query_args = [];

    /**
     * List all supported methods, the first will be the default format.
     *
     * @var array
     */
    protected $_supported_formats = null;

    /**
     * is SSL request
     *
     * @var array
     */
    protected $ssl = false;

    /**
     * Method of request
     *
     * @var array
     */
    protected $method;

    /**
     * If the request is allowed based on the API key provided.
     *
     * @var bool
     */
    protected $_allow = true;

    /**
     * If the request is allowed based on the IP provided.
     *
     * @var bool
     */
    protected $_ipAllow = true;

    /**
     * Information about the current API user.
     *
     * @var object
     */
    protected $_apiuser;

    /**
     * Whether or not to perform a CORS check and apply CORS headers to the request
     *
     * @var bool
     */
    protected $_checkCors = null;

    private $_isValidRequest = true;

    protected $request = null;

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

        helper( 'security' );
	    
        if( class_exists( 'Daycry\\Doctrine\\Doctrine' ) )
        {
            $this->doctrine = \Config\Services::doctrine();
        }
	    
        $formatConfig = config( 'Format' );
        $this->_supported_formats = $formatConfig->supportedResponseFormats;

        $this->request = $request;

        $this->validator =  \Config\Services::validation();
        $this->encryption =  new \Daycry\Encryption\Encryption();
        
        $this->restConfig = config( 'RestServer' );

        // If no Header Accept get default format
        $this->format = $request->negotiate( 'media', $this->_supported_formats );
        $this->setResponseFormat( $this->format );

        // Initialise the response, request and rest objects
        $this->rest = new \stdClass();

        // Check to see if the current IP address is blacklisted
        if( $this->restConfig->restIpBlacklistEnabled === TRUE )
        {
            $this->_checkBlacklistAuth();
            if( !$this->_ipAllow ){ return; }
        }

        // Determine whether the connection is HTTPS
        $this->ssl = $request->isSecure();

        // Check for CORS access request
        $checkCors = $this->restConfig->checkCors;
        if( $checkCors === TRUE )
        {
            $this->_checkCors();
        }

        // Set up the query parameters
        $this->_parse_query();

        // Set up the GET variables
        //var_dump( $this->request->detectPath() );
        $this->_get_args = array_merge( $this->_get_args, $this->_detectSegment() );
        
        // Extend this function to apply additional checking early on in the process
        $this->early_checks();

        // Load DB if its enabled
        if( $this->restConfig->restDatabaseGroup && ( $this->restConfig->restEnableKeys || $this->restConfig->restEnableLogging ) )
        {
            $this->rest->db = \Config\Database::connect( $this->restConfig->restDatabaseGroup );
        }

        // Checking for keys? GET TO WorK!
        if( $this->restConfig->restEnableKeys )
        {
            $this->_allow = $this->_detectApiKey();
        }

        // When there is no specific override for the current class/method, use the default auth value set in the config
        if( ( !( $this->restConfig->restEnableKeys && $this->_allow === true ) || ( $this->restConfig->allowAuthAndKeys === true && $this->_allow === true ) ) )
        {
            $rest_auth = strtolower( $this->restConfig->restAuth );
            switch( $rest_auth )
            {
                case 'basic':
                    $this->_prepareBasicAuth();
                    break;
                case 'digest':
                    $this->_prepare_digest_auth();
                    break;
                /*case 'session':
                    $this->_check_php_session();
                    break;*/
            }

            if( $this->restConfig->restIpWhitelistEnabled === true )
            {
                $this->_checkWhitelistAuth();
            }
        }

        // Try to find a format for the request (means we have a request body)
        $this->inputFormat = $this->_detectInputFormat();
        $this->method  = $request->getMethod();
        $this->headers = $request->getHeaders();
        $this->lang = $request->getLocale();

        if( $this->inputFormat == 'application/json' )
        {
            $this->content = $request->getJSON();
        }else{
            $this->content = $request->getRawInput();
        }
    }

    /**
     * Detect de query segment
     *
     * @return array
     */
    protected function _detectSegment()
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
     * Prepares for basic authentication
     *
     * @access protected
     * @return void
     */
    protected function _prepareBasicAuth()
    {
        // If whitelist is enabled it has the first chance to kick them out
        if( $this->restConfig->restIpWhitelistEnabled )
        {
            $this->_ipAllow = $this->_checkWhitelistAuth();
        }
        if( !$this->_ipAllow ){ $this->_isValidRequest = false; return false; }

        // Returns NULL if the SERVER variables PHP_AUTH_USER and HTTP_AUTHENTICATION don't exist
        $username = $this->request->getServer( 'PHP_AUTH_USER' );
        $http_auth = $this->request->getServer( 'HTTP_AUTHENTICATION' ) ?: $this->request->getServer( 'HTTP_AUTHORIZATION' );

        $password = NULL;
        if ($username !== NULL)
        {
            $password = $this->request->getServer( 'PHP_AUTH_PW' );
        }
        elseif ($http_auth !== NULL)
        {
            // If the authentication header is set as basic, then extract the username and password from
            // HTTP_AUTHORIZATION e.g. my_username:my_password. This is passed in the .htaccess file
            if( strpos( strtolower( $http_auth ), 'basic' ) === 0) 
            {
                // Search online for HTTP_AUTHORIZATION workaround to explain what this is doing
                list( $username, $password ) = explode(':', base64_decode( substr( $this->request->getServer( 'HTTP_AUTHORIZATION' ), 6 ) ) );
            }
        }

        // Check if the user is logged into the system
        if( $this->_checkLogin( $username, $password ) === false )
        {
            $this->_forceLogin();
        }
    }

        /**
     * Prepares for digest authentication
     *
     * @access protected
     * @return void
     */
    protected function _prepare_digest_auth()
    {
        // If whitelist is enabled it has the first chance to kick them out
        if( $this->restConfig->restIpWhitelistEnabled )
        {
            $this->_ipAllow = $this->_checkWhitelistAuth();
        }
        if( !$this->_ipAllow ){ return false; }

        // We need to test which server authentication variable to use,
        // because the PHP ISAPI module in IIS acts different from CGI
        $digest_string = $this->request->getServer( 'PHP_AUTH_DIGEST' );
        if( $digest_string === null )
        {
            $digest_string = $this->request->getServer('HTTP_AUTHORIZATION');
        }

        $unique_id = uniqid();

        // The $_SESSION['error_prompted'] variable is used to ask the password
        // again if none given or if the user enters wrong auth information
        if( empty( $digest_string ) )
        {
            $this->_forceLogin( $unique_id );
        }

        // We need to retrieve authentication data from the $digest_string variable
        $matches = [];
        preg_match_all( '@(username|nonce|uri|nc|cnonce|qop|response)=[\'"]?([^\'",]+)@', $digest_string, $matches );
        $digest = ( empty( $matches[1] ) || empty( $matches[2] ) ) ? [] : array_combine( $matches[1], $matches[2] );
        
        //Workaround for access atributes
        $username = $uri = $nonce = $nc = $cnonce = $qop = $response = null;
        foreach( $digest as $key => $value ){ ${$key} = $digest[ $key ]; }

        // For digest authentication the library function should return already stored md5(username:restrealm:password) for that username see rest.php::auth_library_function config
        $username = $this->_checkLogin( $username, true );
        if( array_key_exists( 'username', $digest ) === false || $username === false )
        {
            $this->_forceLogin( $unique_id );
        }

        $md5 = md5( strtoupper( $this->request->getMethod() ).':'.$uri );
        $valid_response = md5( $username . ':' . $nonce . ':' . $nc . ':' . $cnonce . ':'.$qop . ':' . $md5 );

        // Check if the string don't compare (case-insensitive)
        if( strcasecmp( $response, $valid_response ) !== 0 )
        {
            $this->_isValidRequest = false;
        }
    }

    /**
     * Check if the user is logged in
     *
     * @access protected
     * @param string $username The user's name
     * @param bool|string $password The user's password
     * @return bool
     */
    protected function _checkLogin( $username = null, $password = false )
    {
        if( empty( $username ) )
        {
            $this->_isValidRequest = false;
            return false;
        }

        $auth_source = strtolower( $this->restConfig->authSource );
        $rest_auth = strtolower( $this->restConfig->restAuth );
        $valid_logins = $this->restConfig->restValidLogins;

        if( !$this->restConfig->authSource && $rest_auth === 'digest' )
        {
            // For digest we do not have a password passed as argument
            return md5( $username . ':' . $this->restConfig->restRealm . ':' . ( isset( $valid_logins[ $username ] ) ? $valid_logins[ $username ] : '' ) );
        }

        if( $password === false )
        {
            $this->_isValidRequest = false;
            return false;
        }

        /*if( $auth_source === 'ldap' )
        {
            log_message('debug', "Performing LDAP authentication for $username");

            return $this->_perform_ldap_auth($username, $password);
        }*/

        /*if ($auth_source === 'library')
        {
            log_message('debug', "Performing Library authentication for $username");

            return $this->_perform_library_auth($username, $password);
        }*/

        if( array_key_exists( $username, $valid_logins ) === false )
        {
            $this->_isValidRequest = false;
            return false;
        }

        if( $valid_logins[ $username ] !== $password )
        {
            $this->_isValidRequest = false;
            return false;
        }

        return true;
    }

    /**
     * Force logging in by setting the WWW-Authenticate header
     *
     * @access protected
     * @param string $nonce A server-specified data string which should be uniquely generated
     * each time
     * @return void
     */
    protected function _forceLogin( $nonce = '' )
    {
        $rest_auth = $this->restConfig->restAuth;
        $rest_realm = $this->restConfig->restRealm;
        if( strtolower( $rest_auth ) === 'basic' )
        {
            // See http://tools.ietf.org/html/rfc2617#page-5
            header('WWW-Authenticate: Basic realm="' . $rest_realm . '"');
        }
        elseif( strtolower( $rest_auth ) === 'digest' )
        {
            // See http://tools.ietf.org/html/rfc2617#page-18
            header(
                'WWW-Authenticate: Digest realm="' . $rest_realm
                . '", qop="auth", nonce="' . $nonce
                . '", opaque="' . md5( $rest_realm ) . '"');
        }

        if( $this->restConfig->strictApiAndAuth === true )
        {
            $this->_isValidRequest = false;
        }
    }

    /**
     * Check if the client's ip is in the 'rest_ip_whitelist' config and generates a 401 response
     *
     * @access protected
     * @return void
     */
    protected function _checkWhitelistAuth()
    {
        $whitelist = explode( ',', $this->restConfig->restIpWhitelist );

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
     * Checks if the client's ip is in the 'rest_ip_blacklist' config and generates a 401 response
     *
     * @access protected
     * @return void
     */
    protected function _checkBlacklistAuth()
    {
        // Match an ip address in a blacklist e.g. 127.0.0.0, 0.0.0.0
        $pattern = sprintf( '/(?:,\s*|^)\Q%s\E(?=,\s*|$)/m', $this->request->getIPAddress() );

        // Returns 1, 0 or FALSE (on error only). Therefore implicitly convert 1 to TRUE
        if ( preg_match( $pattern, $this->restConfig->restIpBlacklist ) )
        {
            $this->_ipAllow = false;
        }
    }

    /**
     * Get the input format e.g. json or xml.
     *
     * @return string|null Supported input format; otherwise, NULL
     */
    protected function _detectInputFormat()
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
     * Checks allowed domains, and adds appropriate headers for HTTP access control (CORS)
     *
     * @access protected
     * @return void
     */
    protected function _checkCors()
    {
        // Convert the config items into strings
        $allowed_headers = implode( ', ', $this->restConfig->allowedCorsHeaders );
        $allowed_methods = implode( ', ', $this->restConfig->allowedCorsMethods );

        // If we want to allow any domain to access the API
        if( $this->restConfig->allowAnyCorsDomain === true )
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
            if( in_array( $origin, $this->restConfig->allowedCorsOrigins ) )
            {
                header( 'Access-Control-Allow-Origin: ' . $origin );
                header( 'Access-Control-Allow-Headers: ' . $allowed_headers );
                header( 'Access-Control-Allow-Methods: ' . $allowed_methods );
            }
        }

        // If the request HTTP method is 'OPTIONS', kill the response and send it to the client
        if( $this->request->getMethod() === 'options' )
        {
            exit;
        }
    }

    /**
     * See if the user has provided an API key.
     *
     * @return bool
     */
    protected function _detectApiKey()
    {
        // Get the api key name variable set in the rest config file
        $api_key_variable = $this->restConfig->restKeyName;

        // Work out the name of the SERVER entry based on config
        $key_name = 'HTTP_' . strtoupper( str_replace( '-', '_', $api_key_variable ) );

        $this->rest->key = null;
        $this->rest->level = null;
        $this->rest->user_id = null;
        $this->rest->ignore_limits = false;

        // Find the key from server or arguments
        if( ( $this->rest->key = isset( $this->_args[ $api_key_variable ] ) ? $this->_args[ $api_key_variable ] : $this->request->getServer( $key_name ) ) )
        {
            if( !( $row = ( $this->rest->db->table( $this->restConfig->restKeysTable )->getWhere( [ $this->restConfig->restKeyColumn => $this->rest->key ] )->getRow() ) ) )
            {
                return false;
            }

            $this->rest->key = $row->{ $this->restConfig->restKeyColumn };

            isset($row->user_id) && $this->rest->user_id = $row->user_id;
            isset($row->level) && $this->rest->level = $row->level;
            isset($row->ignore_limits) && $this->rest->ignore_limits = $row->ignore_limits;

            $this->_apiuser = $row;

            /*
             * If "is private key" is enabled, compare the ip address with the list
             * of valid ip addresses stored in the database
             */
            if( empty( $row->is_private_key ) === false )
            {
                // Check for a list of valid ip addresses
                if( isset( $row->ip_addresses ) )
                {
                    // multiple ip addresses must be separated using a comma, explode and loop
                    $list_ip_addresses = explode( ',', $row->ip_addresses );
                    $ip_address = $this->request->getIPAddress();
                    $found_address = false;

                    foreach( $list_ip_addresses as $list_ip )
                    {
                        if ( $ip_address === trim( $list_ip ) )
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
     * Parse the query parameters.
     *
     * @return void
     */
    protected function _parse_query()
    {
        $this->_query_args = $this->request->getGet();
    }

    /**
     * Parse the GET request arguments.
     *
     * @return void
     */
    protected function _parse_get()
    {
        if( $this->format )
        {
            $this->body = $this->request->getRawInput();
        }

        // Merge both the URI segments and query parameters
        $this->_get_args = array_merge( $this->_get_args, $this->_query_args );
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

        if( $this->restConfig->forceHttps && $this->ssl === false )
        {
            return $this->failForbidden( lang( 'Rest.textRestUnsupported' ) );
        }

        // They provided a key, but it wasn't valid, so get them out of here
        if( $this->restConfig->restEnableKeys && $this->_allow === false )
        {
            return $this->failUnauthorized( $parser->setData( array( 'key' => $this->rest->key) )->renderString( lang( 'Rest.textRestInvalidApiKey' ) ) );
        }

        if( $this->_ipAllow === false )
        {
            return $this->failUnauthorized( lang( 'Rest.ipDenied' ) );
        }

        if( !$this->_isValidRequest )
        {
            return $this->failUnauthorized( lang( 'Rest.textUnauthorized' ) );
        }

        if( $validation != null )
        {
            if( !$this->validator->run( (array)$this->content, $validation ) )
            {
                return $this->fail( $this->validator->getErrors()  );
            }
        }

        return true;
    }
}
