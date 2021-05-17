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
    public $restRealm = '2FA SERVICE';

    /*
    |--------------------------------------------------------------------------
    | REST Handle Exceptions
    |--------------------------------------------------------------------------
    |
    | Handle exceptions caused by the controller
    |
    */
    public $restHandleExceptions = true;

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
    | 'jwt'  JWT Token
    | 'session' Check for a PHP session variable. See 'auth_source' to set the
    |           authorization key
    |
    */
    public $restAuth = 'basic';

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
    | Note: If 'rest_auth' is set to 'session' then change 'auth_source' to the name of the session variable
    |
    */
    public $authSource = '';

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
    | REST Login Class and Function
    |--------------------------------------------------------------------------
    |
    | If library authentication is used define the class and function name
    |
    | The function should accept two parameters: class->function($username, $password)
    | In other cases override the function _perform_library_auth in your controller
    |
    | For digest authentication the library function should return already a stored
    | md5(username:restrealm:password) for that username
    |
    | e.g: md5('admin:REST API:1234') = '1e957ebc35631ab22d5bd6526bd14ea2'
    |
    */
    public $authLibraryClass = \Daycry\RestServer\Libraries\JWT::class;
    public $authLibraryFunction = 'decode';

    /*
    |--------------------------------------------------------------------------
    | Override auth types for specific class/method
    |--------------------------------------------------------------------------
    |
    | Set specific authentication types for methods within a class (controller)
    |
    | Set as many config entries as needed.  Any methods not set will use the default 'rest_auth' config value.
    |
    | e.g:
    |
    |           public $authOverrideClassMethod = array
                (
                    '\Ldap\Controllers\Search' => array
                    ( 
                        'index' => 'none'
                    )
                );
    |
    | Here 'deals', 'accounts' and 'dashboard' are controller names, 'view', 'insert' and 'user' are methods within. An asterisk may also be used to specify an authentication method for an entire classes methods. Ex: $config['auth_override_class_method']['dashboard']['*'] = 'basic'; (NOTE: leave off the '_get' or '_post' from the end of the method name)
    | Acceptable values are; 'none', 'digest' and 'basic'.
    |
    */

    //public $authOverrideClassMethod[ '\Ldap\Controllers\Search' ][ 'index' ] = 'none';

    // ---Uncomment list line for the wildard unit test
    // $this->authOverrideClassMethod['wildcard_test_cases']['*'] = 'basic';

    /*
    |--------------------------------------------------------------------------
    | Override auth types for specfic 'class/method/HTTP method'
    |--------------------------------------------------------------------------
    |
    | example:
    |
    |           public $authOverrideClassMethodHttp = array
                (
                    '\Ldap\Controllers\Search' => array
                    ( 
                        'index' 
                    )
                );
    */

    // ---Uncomment list line for the wildard unit test
    // $this->authOverrideClassMethodHttp['wildcard_test_cases']['*']['options'] = 'basic';

    /*
    |--------------------------------------------------------------------------
    | REST Login Usernames
    |--------------------------------------------------------------------------
    |
    | Array of usernames and passwords for login, if ldap is configured this is ignored
    |
    */
    public $restValidLogins = ['admin' => '1234'];

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
    | 3. Set to FALSE but set 'auth_override_class_method' to 'whitelist' to
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
    | 1. Set to TRUE and add any IP address to 'rest_ip_blacklist'
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
    public $restDatabaseGroup = 'api';

    /*
    |--------------------------------------------------------------------------
    | REST API Keys Table Name
    |--------------------------------------------------------------------------
    |
    | The table name in your database that stores API keys
    |
    */
    public $restKeysTable = 'ws_keys';

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
    public $restEnableKeys = true;

    /*
    |--------------------------------------------------------------------------
    | REST Table Key Column Name
    |--------------------------------------------------------------------------
    |
    | If not using the default table schema in 'rest_enable_keys', specify the
    | column name to match e.g. my_key
    |
    */
    public $restKeyColumn = 'app_key';

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
    public $restKeyName = '2FA-API-KEY';

    /*
    |--------------------------------------------------------------------------
    | REST Enable Logging
    |--------------------------------------------------------------------------
    |
    | When set to TRUE, the REST API will log actions based on the column names 'key', 'date',
    | 'time' and 'ip_address'. This is a general rule that can be overridden in the
    | $this->method array for each controller
    |
    | Default table schema:
    |   CREATE TABLE `logs` (
    |       `id` INT(11) NOT NULL AUTO_INCREMENT,
    |       `uri` VARCHAR(255) NOT NULL,
    |       `method` VARCHAR(6) NOT NULL,
    |       `params` TEXT DEFAULT NULL,
    |       `api_key` VARCHAR(40) NOT NULL,
    |       `ip_address` VARCHAR(45) NOT NULL,
    |       `time` INT(11) NOT NULL,
    |       `rtime` FLOAT DEFAULT NULL,
    |       `authorized` VARCHAR(1) NOT NULL,
    |       `response_code` smallint(3) DEFAULT '0',
    |       PRIMARY KEY (`id`)
    |   ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    |
    */
    public $restEnableLogging = false;

    /*
    |--------------------------------------------------------------------------
    | REST API Logs Table Name
    |--------------------------------------------------------------------------
    |
    | If not using the default table schema in 'rest_enable_logging', specify the
    | table name to match e.g. my_logs
    |
    */
    public $configrestLogsTable = 'logs';

    /*
    |--------------------------------------------------------------------------
    | REST API Param Log Format
    |--------------------------------------------------------------------------
    |
    | When set to TRUE, the REST API log parameters will be stored in the database as JSON
    | Set to FALSE to log as serialized PHP
    |
    */
    public $restLogsJsonParams = false;

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
    public $checkCors = true;

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
        '2FA-API-KEY',
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
}
