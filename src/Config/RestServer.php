<?php

namespace Daycry\RestServer\Config;

use CodeIgniter\Config\BaseConfig;

class RestServer extends BaseConfig
{
    /*
    |--------------------------------------------------------------------------
    | REST Realm
    |--------------------------------------------------------------------------
    |
    | Name of the password protected REST API displayed on login dialogs
    |
    | e.g: My Secret REST API
    |
    */
    public string $restRealm = 'WEB SERVICE';

    /*
    |--------------------------------------------------------------------------
    | REST Login
    |--------------------------------------------------------------------------
    |
    | Set to specify the REST API requires to be logged in
    |
    | FALSE     No login required
    | 'basic'   Unsecure login
    | 'digest'  More secure login
    | 'bearer'     Bearer Token
    | 'session' Check for a PHP session variable. See 'auth_source' to set the
    |           authorization key
    |
    */
    public $restAuth = false;

    public array $restAuthClassMap =
    [
        'basic' => \Daycry\RestServer\Libraries\Auth\BasicAuth::class,
        'digest' => \Daycry\RestServer\Libraries\Auth\DigestAuth::class,
        'bearer' => \Daycry\RestServer\Libraries\Auth\BearerAuth::class,
        'session' => \Daycry\RestServer\Libraries\Auth\SessionAuth::class
    ];

    /*
    |--------------------------------------------------------------------------
    | REST Login Source
    |--------------------------------------------------------------------------
    |
    | Is login required and if so, the user store to use
    |
    | ''        Use config based users or wildcard testing
    | 'ldap'    Use LDAP authentication
    | 'library' Use a authentication library
    |
    | Note: If 'restAuth' is set to 'session' then change 'authSource' to the name of the session variable
    |
    | If library authentication is used define the class and function name
    |
    | The function should accept two parameters: class->function($username, $password)
    |
    | For digest authentication the library function should return already a stored
    | md5(username:restrealm:password) for that username
    |
    | e.g: md5('admin:REST API:1234') = '1e957ebc35631ab22d5bd6526bd14ea2'
    |
    */
    public string $authSource = '';

    /*
    |--------------------------------------------------------------------------
    | Custom Auth Library
    |--------------------------------------------------------------------------
    |
    | Each validation method allows you to configure your own custom library,
    | it is useful when using different validation methods according to each api call.
    |
        <?php

        namespace Auth\Libraries;

        use Daycry\RestServer\Interfaces\LibraryAuthInterface;
        use CodeIgniter\Config\BaseConfig;

        class CheckAuth implements LibraryAuthInterface
        {
            public function validate( $username, $password = true )
            {
                if( $username != 'admin' )
                {
                    throw \Daycry\RestServer\Exceptions\UnauthorizedException::forInvalidCredentials();
                }

                return $username;
            }
        }
    */

    public array $authLibraryClass =
    [
        'basic' => null, // \Daycry\RestServer\Libraries\AuthClass::class
        'digest' => null, // \Daycry\RestServer\Libraries\AuthClass::class
        'bearer' => null // \Daycry\RestServer\Libraries\AuthClass::class
    ];

    /**
     * @deprecated, the method name must be 'validate'
     */
    //public $authLibraryFunction = 'validate';

    /**
    *--------------------------------------------------------------------------
    * Enable block Invalid Attempts
    *--------------------------------------------------------------------------
    *
    * IP blocking on consecutive failed attempts
    *
    */
    public bool $restEnableInvalidAttempts = true;
    public string $restInvalidAttemptsTable = 'ws_attempt';
    public int $restMaxAttempts = 3;
    public int $restTimeBlocked = 3600;

    /**
    *--------------------------------------------------------------------------
    * REST AJAX Only
    *--------------------------------------------------------------------------
    *
    * Set to TRUE to allow AJAX requests only. Set to FALSE to accept HTTP requests
    *
    * Note: If set to TRUE and the request is not AJAX, a 505 response with the
    * error message 'Only AJAX requests are accepted.' will be returned.
    *
    * Hint: This is good for production environments
    *
    */
    public bool $restAjaxOnly = false;

    /**
    *--------------------------------------------------------------------------
    * Allow Authentication and API Keys
    *--------------------------------------------------------------------------
    *
    * Where you wish to have Basic, Digest or Session login, but also want to use API Keys (for limiting
    * requests etc), set to TRUE;
    *
    */
    public bool $allowAuthAndKeys = true;
    public bool $strictApiAndAuth = true; // force the use of both api and auth before a valid api request is made

    /**
    *--------------------------------------------------------------------------
    * REST Login Usernames
    *--------------------------------------------------------------------------
    *
    * Array of usernames and passwords for login, if ldap is configured this is ignored
    *
    */
    public array $restValidLogins = [ 'admin' => '1234' ];

    /*
    |--------------------------------------------------------------------------
    | Global IP Whitelisting
    |--------------------------------------------------------------------------
    |
    | Limit connections to your REST server to whitelisted IP addresses
    |
    | Usage:
    | 1. Set to TRUE and select an auth option for extreme security (client's IP
    |    address must be in whitelist and they must also log in)
    | 2. Set to TRUE with auth set to FALSE to allow whitelisted IPs access with no login
    | 3. Set to FALSE but set 'restEnableOverridePetition' to 'whitelist' to
    |    restrict certain methods to IPs in your whitelist
    |
    */
    public bool $restIpWhitelistEnabled = false;

    /*
    |--------------------------------------------------------------------------
    | REST IP White-list
    |--------------------------------------------------------------------------
    |
    | Limit connections to your REST server with a comma separated
    | list of IP addresses
    |
    | e.g: '123.456.789.0, 987.654.32.1'
    |
    | 127.0.0.1 and 0.0.0.0 are allowed by default
    |
    */
    public string $restIpWhitelist = '';

    /*
    |--------------------------------------------------------------------------
    | Global IP Blacklisting
    |--------------------------------------------------------------------------
    |
    | Prevent connections to the REST server from blacklisted IP addresses
    |
    | Usage:
    | 1. Set to TRUE and add any IP address to 'restIpBlacklist'
    |
    */
    public bool $restIpBlacklistEnabled = false;

    /*
    |--------------------------------------------------------------------------
    | REST IP Blacklist
    |--------------------------------------------------------------------------
    |
    | Prevent connections from the following IP addresses
    |
    | e.g: '123.456.789.0, 987.654.32.1'
    |
    */
    public string $restIpBlacklist = '';

    /*
    |--------------------------------------------------------------------------
    | REST Database Group
    |--------------------------------------------------------------------------
    |
    | Connect to a database group for keys, logging, etc. It will only connect
    | if you have any of these features enabled
    |
    */
    public string $restDatabaseGroup = 'default';

    /**
    *--------------------------------------------------------------------------
    * REST API Users Table Name
    *--------------------------------------------------------------------------
    *
    * The table name in your database that stores API keys
    *
    */
    public string $restUsersTable = 'ws_user';


    /**
     * Class that associates keys to users, you can edit the class for whatever you want
     * The "$userKeyColumn" column is the foreign key of the keys table
     */
    public $userModelClass = \Daycry\RestServer\Models\UserModel::class;
    public string $userKeyColumn = 'key_id';

    /**
    *--------------------------------------------------------------------------
    * REST API Keys Table Name
    *--------------------------------------------------------------------------
    *
    * The table name in your database that stores API keys
    *
    */
    public string $restKeysTable = 'ws_key';
    public bool $restEnableKeys = false;

    /*
    *--------------------------------------------------------------------------
    * REST Table Key Column Name
    *--------------------------------------------------------------------------
    *
    * If not using the default table schema in 'rest_enable_keys', specify the
    * column name to match e.g. my_key
    *
    */
    public string $restKeyColumn = 'key';

    /*
    *--------------------------------------------------------------------------
    * REST Key Length
    *--------------------------------------------------------------------------
    *
    * Length of the created keys. Check your default database schema on the
    * maximum length allowed
    *
    * Note: The maximum length is 40
    *
    */
    public int $restKeyLength = 40;

    /*
    *--------------------------------------------------------------------------
    * REST API Key Variable
    *--------------------------------------------------------------------------
    *
    * Custom header to specify the API key

    * Note: Custom headers with the X- prefix are deprecated as of
    * 2012/06/12. See RFC 6648 specification for more details
    *
    */
    public string $restKeyName = 'X-API-KEY';


    /*
    |--------------------------------------------------------------------------
    | REST Petitions
    |--------------------------------------------------------------------------
    |
    | When set to TRUE, the REST API will look for an override method
    |
    */
    public bool $restEnableOverridePetition = false;

    /*
    |--------------------------------------------------------------------------
    | REST API Petition Table Name
    |--------------------------------------------------------------------------
    |
    | If not using the default table schema in 'petitions', specify the
    | table name to match e.g. my_operations
    |
    */
    public string $configRestPetitionsTable = 'ws_request';

    /*
    |--------------------------------------------------------------------------
    | REST Enable Logging
    |--------------------------------------------------------------------------
    |
    | When set to TRUE, the REST API will log actions based on the column names 'key', 'date',
    | 'time' and 'ip_address'. This is a general rule that can be overridden in the
    | $this->method array for each controller
    |
    */
    public bool $restEnableLogging = false;

    /*
    |--------------------------------------------------------------------------
    | REST API Logs Table Name
    |--------------------------------------------------------------------------
    |
    | If not using the default table schema in 'restEnableLogging', specify the
    | table name to match e.g. my_logs
    |
    */
    public string $configRestLogsTable = 'ws_log';

    /*
    |--------------------------------------------------------------------------
    | REST API Param Log Format
    |--------------------------------------------------------------------------
    |
    | When set to TRUE, the REST API log parameters will be stored in the database as JSON
    | Set to FALSE to log as serialized PHP
    |
    */
    public bool $restLogsJsonParams = true;

    /*
    |--------------------------------------------------------------------------
    | REST API Param Log Encrypt
    |--------------------------------------------------------------------------
    |
    | When set to TRUE, the REST API log parameters will be encrypt.
    |
    */
    public bool $restEncryptLogParams = false;

    /*
    |--------------------------------------------------------------------------
    | REST Enable Limits
    |--------------------------------------------------------------------------
    |
    | When set to TRUE, the REST API will count the number of uses of each method
    | by an API key each hour. This is a general rule that can be overridden in the
    | $this->method array in each controller
    |
    */
    public bool $restEnableLimits = false;

    /*
    |--------------------------------------------------------------------------
    | REST API Limits Table Name
    |--------------------------------------------------------------------------
    |
    | If not using the default table schema in 'restEnableLimits', specify the
    | table name to match e.g. my_limits
    |
    */
    public string $restLimitsTable = 'ws_limit';

    /*
    |--------------------------------------------------------------------------
    | REST API Limits method
    |--------------------------------------------------------------------------
    |
    | Specify the method used to limit the API calls
    |
    | Available methods are :
    | public $restLimitsMethod = 'IP_ADDRESS'; // Put a limit per ip address
    | public $restLimitsMethod = 'API_KEY'; // Put a limit per api key
    | public $restLimitsMethod = 'METHOD_NAME'; // Put a limit on method calls
    | public $restLimitsMethod = 'ROUTED_URL';  // Put a limit on the routed URL
    |
    */
    public string $restLimitsMethod = 'METHOD_NAME';

    /*
    |--------------------------------------------------------------------------
    | REST Method Access Control
    |--------------------------------------------------------------------------
    | When set to TRUE, the REST API will check the access table to see if
    | the API key can access that controller. 'restEnableKeys' must be enabled
    | to use this.
    | You can filter an access with controller method when the 'all_access' field is '0'
    |
    */
    public bool $restEnableAccess = false;

    /*
    |--------------------------------------------------------------------------
    | REST API Access Table Name
    |--------------------------------------------------------------------------
    |
    | If not using the default table schema in 'rest_enable_access', specify the
    | table name to match e.g. my_access
    |
    */
    public string $restAccessTable = 'ws_access';

    /*
    |--------------------------------------------------------------------------
    | CORS Check
    |--------------------------------------------------------------------------
    |
    | Set to TRUE to enable Cross-Origin Resource Sharing (CORS). Useful if you
    | are hosting your API on a different domain from the application that
    | will access it through a browser
    |
    */
    public bool $checkCors = false;

    /*
    |--------------------------------------------------------------------------
    | CORS Allowable Headers
    |--------------------------------------------------------------------------
    |
    | If using CORS checks, set the allowable headers here
    |
    */
    public array $allowedCorsHeaders = [
        'Origin',
        'X-Requested-With',
        'Content-Type',
        'Accept',
        'Access-Control-Request-Method',
        'X-API-KEY',
        'Authorization'
    ];

    /*
    |--------------------------------------------------------------------------
    | CORS Allowable Methods
    |--------------------------------------------------------------------------
    |
    | If using CORS checks, you can set the methods you want to be allowed
    |
    */
    public array $allowedCorsMethods = [
        'GET',
        'POST',
        'OPTIONS',
        'PUT',
        'PATCH',
        'DELETE'
    ];

    /*
    |--------------------------------------------------------------------------
    | CORS Allow Any Domain
    |--------------------------------------------------------------------------
    |
    | Set to TRUE to enable Cross-Origin Resource Sharing (CORS) from any
    | source domain
    |
    */
    public bool $allowAnyCorsDomain = false;

    /*
    |--------------------------------------------------------------------------
    | CORS Allowable Domains
    |--------------------------------------------------------------------------
    |
    | Used if $config['check_cors'] is set to TRUE and $config['allow_any_cors_domain']
    | is set to FALSE. Set all the allowable domains within the array
    |
    | e.g. $allowedCorsOrigins = ['http://www.example.com', 'https://spa.example.com']
    | e.g. $allowedCorsOrigins = 'http://www.example.com'
    |
    */
    public $allowedCorsOrigins = '';

    /*
    |--------------------------------------------------------------------------
    | CORS Forced Headers
    |--------------------------------------------------------------------------
    |
    | If using CORS checks, always include the headers and values specified here
    | in the OPTIONS client preflight.
    | Example:
    | $config['forcedCorsHeaders'] = [
    |   'Access-Control-Allow-Credentials' => 'true'
    | ];
    |
    | Added because of how Sencha Ext JS framework requires the header
    | Access-Control-Allow-Credentials to be set to true to allow the use of
    | credentials in the REST Proxy.
    | See documentation here:
    | http://docs.sencha.com/extjs/6.5.2/classic/Ext.data.proxy.Rest.html#cfg-withCredentials
    |
    */
    public array $forcedCorsHeaders = [ 'Access-Control-Allow-Credentials' => true ];

    /**
    *--------------------------------------------------------------------------
    * Cronjob
    *--------------------------------------------------------------------------
    *
    * Set to TRUE to enable Cronjob for fill the table petitions with your API classes
    * $restNamespaceScope \Namespace\Class or \Namespace\Folder\Class or \Namespace example: \App\Controllers
    *
    */
    public bool $restEnableCronjob = true;
    public string $restNamespaceTable = 'ws_namespace';
    public array $restNamespaceScope = ['\Daycry\JWT'];


}
