<?php namespace Daycry\RestServer\Config;

use CodeIgniter\Config\BaseConfig;

class RestServer extends BaseConfig
{
    /*
    |--------------------------------------------------------------------------
    | HTTP protocol
    |--------------------------------------------------------------------------
    |
    | Set to force the use of HTTPS for REST API calls
    |
    | @deprecated you can must forceGlobalSecureRequests in App.php
    */
    public $forceHttps = true;

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
    public $restRealm = 'WEB SERVICE';

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

    public $restAuthClassMap = 
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
    */
    public $authSource = '';

    /*
    |--------------------------------------------------------------------------
    | REST Login Class and Function
    |--------------------------------------------------------------------------
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

        class CheckAuth implements LibraryAuthInterface
        {
            public function __construct()
            {
            }

            public function validate( $username, $password = true )
            {
                if( $username != 'admin )
                {
                    throw \Daycry\RestServer\Exceptions\UnauthorizedException::forInvalidCredentials();
                }
            }
        }
    */
	
    public $authLibraryClass = 
    [
        'basic' => null, // \Daycry\RestServer\Libraries\AuthClass::class
        'digest' => null, // \Daycry\RestServer\Libraries\AuthClass::class
        'bearer' => null // \Daycry\RestServer\Libraries\AuthClass::class
    ];

    public $authLibraryFunction = 'validate';

    /*
    |--------------------------------------------------------------------------
    | Allow Authentication and API Keys
    |--------------------------------------------------------------------------
    |
    | Where you wish to have Basic, Digest or Session login, but also want to use API Keys (for limiting
    | requests etc), set to TRUE;
    |
    */
    public $allowAuthAndKeys = true;
    public $strictApiAndAuth = true; // force the use of both api and auth before a valid api request is made
    
    /*
    |--------------------------------------------------------------------------
    | REST Login Usernames
    |--------------------------------------------------------------------------
    |
    | Array of usernames and passwords for login, if ldap is configured this is ignored
    |
    */
    public $restValidLogins = [ 'admin' => '1234' ];

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
    public $restIpWhitelistEnabled = false;

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
    public $restIpWhitelist = '';

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
    public $restIpBlacklistEnabled = false;

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
    public $restIpBlacklist = '';

    /*
    |--------------------------------------------------------------------------
    | REST Database Group
    |--------------------------------------------------------------------------
    |
    | Connect to a database group for keys, logging, etc. It will only connect
    | if you have any of these features enabled
    |
    */
    public $restDatabaseGroup = 'default';

    /*
    |--------------------------------------------------------------------------
    | REST API Users Table Name
    |--------------------------------------------------------------------------
    |
    | The table name in your database that stores API keys
    |
    */
    public $restUsersTable = 'restserver_user';


    /**
     * If you want to link users with the keys you have to assign the user model
     * 
     * Example:
     * 
     * public $userModelClass = \Daycry\RestServer\Models\UserModel::class;
     */
    public $userModelClass = \Daycry\RestServer\Models\UserModel::class;
    public $userKeyColumn = 'key_id';
    
    /*
    |--------------------------------------------------------------------------
    | REST API Keys Table Name
    |--------------------------------------------------------------------------
    |
    | The table name in your database that stores API keys
    |
    */
    public $restKeysTable = 'restserver_key';

    /*
    |--------------------------------------------------------------------------
    | REST Enable Keys
    |--------------------------------------------------------------------------
    |
    | When set to TRUE, the REST API will look for a column name called 'key'.
    | If no key is provided, the request will result in an error. To override the
    | column name see 'rest_key_column'
    |
    | Default table schema:
    |   CREATE TABLE `keys` (
    |       `id` INT(11) NOT NULL AUTO_INCREMENT,
    |       `user_id` INT(11) NOT NULL,
    |       `key` VARCHAR(40) NOT NULL,
    |       `level` INT(2) NOT NULL,
    |       `ignore_limits` TINYINT(1) NOT NULL DEFAULT '0',
    |       `is_private_key` TINYINT(1)  NOT NULL DEFAULT '0',
    |       `ip_addresses` TEXT NULL DEFAULT NULL,
    |       `date_created` INT(11) NOT NULL,
    |       PRIMARY KEY (`id`)
    |   ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    |
    */
    public $restEnableKeys = false;

    /*
    |--------------------------------------------------------------------------
    | REST Table Key Column Name
    |--------------------------------------------------------------------------
    |
    | If not using the default table schema in 'rest_enable_keys', specify the
    | column name to match e.g. my_key
    |
    */
    public $restKeyColumn = 'key';

    /*
    |--------------------------------------------------------------------------
    | REST Key Length
    |--------------------------------------------------------------------------
    |
    | Length of the created keys. Check your default database schema on the
    | maximum length allowed
    |
    | Note: The maximum length is 40
    |
    */
    public $restKeyLength = 40;

    /*
    |--------------------------------------------------------------------------
    | REST API Key Variable
    |--------------------------------------------------------------------------
    |
    | Custom header to specify the API key

    | Note: Custom headers with the X- prefix are deprecated as of
    | 2012/06/12. See RFC 6648 specification for more details
    |
    */
    public $restKeyName = 'X-API-KEY';


    /*
    |--------------------------------------------------------------------------
    | REST Petitions
    |--------------------------------------------------------------------------
    |
    | When set to TRUE, the REST API will look for an override method
    |
    */
    public $restEnableOverridePetition = false;

    /*
    |--------------------------------------------------------------------------
    | REST API Petition Table Name
    |--------------------------------------------------------------------------
    |
    | If not using the default table schema in 'petitions', specify the
    | table name to match e.g. my_operations
    |
    */
    public $configRestPetitionsTable = 'restserver_petition';

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
    public $restEnableLogging = false;

    /*
    |--------------------------------------------------------------------------
    | REST API Logs Table Name
    |--------------------------------------------------------------------------
    |
    | If not using the default table schema in 'restEnableLogging', specify the
    | table name to match e.g. my_logs
    |
    */
    public $configRestLogsTable = 'restserver_log';

    /*
    |--------------------------------------------------------------------------
    | REST API Param Log Format
    |--------------------------------------------------------------------------
    |
    | When set to TRUE, the REST API log parameters will be stored in the database as JSON
    | Set to FALSE to log as serialized PHP
    |
    */
    public $restLogsJsonParams = true;

    /*
    |--------------------------------------------------------------------------
    | REST API Param Log Encrypt
    |--------------------------------------------------------------------------
    |
    | When set to TRUE, the REST API log parameters will be encrypt.
    |
    */
    public $restEncryptLogParams = true;

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
    public $restEnableLimits = false;

    /*
    |--------------------------------------------------------------------------
    | REST API Limits Table Name
    |--------------------------------------------------------------------------
    |
    | If not using the default table schema in 'restEnableLimits', specify the
    | table name to match e.g. my_limits
    |
    */
    public $restLimitsTable = 'resetserver_limit';

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
    public $restLimitsMethod = 'API_KEY';

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
    public $restEnableAccess = false;

    /*
    |--------------------------------------------------------------------------
    | REST API Access Table Name
    |--------------------------------------------------------------------------
    |
    | If not using the default table schema in 'rest_enable_access', specify the
    | table name to match e.g. my_access
    |
    */
    public $restAccessTable = 'restserver_access';

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
    public $checkCors = false;

    /*
    |--------------------------------------------------------------------------
    | CORS Allowable Headers
    |--------------------------------------------------------------------------
    |
    | If using CORS checks, set the allowable headers here
    |
    */
    public $allowedCorsHeaders = [
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
    public $allowedCorsMethods = [
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
    public $allowAnyCorsDomain = false;

    /*
    |--------------------------------------------------------------------------
    | CORS Allowable Domains
    |--------------------------------------------------------------------------
    |
    | Used if $config['check_cors'] is set to TRUE and $config['allow_any_cors_domain']
    | is set to FALSE. Set all the allowable domains within the array
    |
    | e.g. $config['allowed_origins'] = ['http://www.example.com', 'https://spa.example.com']
    |
    */
    public $allowedCorsOrigins = [];

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
    public $forcedCorsHeaders = [];
}