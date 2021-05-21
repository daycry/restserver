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
    | 'jwt'  JWT Token
    | 'session' Check for a PHP session variable. See 'auth_source' to set the
    |           authorization key
    |
    */
    public $restAuth = false;

    public $restAuthClassMap = 
    [
        'basic' => \Daycry\RestServer\Libraries\Auth\BasicAuth::class,
        'digest' => \Daycry\RestServer\Libraries\Auth\DigestAuth::class,
        'jwt' => \Daycry\RestServer\Libraries\Auth\JWTAuth::class,
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
    | 3. Set to FALSE but set 'auth_override_class_method' to 'whitelist' to
    |    restrict certain methods to IPs in your whitelist
    |
    */
    public $restIpWhitelistEnabled = true;

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
    public $restIpWhitelist = '10.33.24.15';

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
    public $restKeysTable = 'keys';

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
    | When set to TRUE, the REST API will look for a column name called 'key'.
    | If no key is provided, the request will result in an error. To override the
    | column name see 'rest_key_column'
    |
    | Default table schema:
    |   CREATE TABLE `petitions` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `controller` VARCHAR(100) NOT NULL COLLATE 'utf8_general_ci',
            `method` VARCHAR(100) NOT NULL DEFAULT '*' COLLATE 'utf8_general_ci',
            `http` VARCHAR(10) NOT NULL DEFAULT '*' COLLATE 'utf8_general_ci',
            `auth` VARCHAR(10) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
            `log` TINYINT(1) NULL DEFAULT NULL,
            `limit` TINYINT(1) NULL DEFAULT NULL,
            `level` TINYINT(1) NULL DEFAULT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `deleted_at` DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (`id`) USING BTREE,
            UNIQUE INDEX `controller` (`controller`, `method`, `http`) USING BTREE,
            INDEX `deleted_at` (`deleted_at`) USING BTREE
        )
        COLLATE='utf8_general_ci'
        ENGINE=InnoDB
        AUTO_INCREMENT=1
        ;
;
    |
    */
    public $restEnableOverridePetition = true;

    /*
    |--------------------------------------------------------------------------
    | REST API Operations Table Name
    |--------------------------------------------------------------------------
    |
    | If not using the default table schema in 'restEnableOperations', specify the
    | table name to match e.g. my_operations
    |
    */
    public $configRestPetitionsTable = 'petitions';

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
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `uri` VARCHAR(255) NOT NULL COLLATE 'utf8_general_ci',
            `method` VARCHAR(6) NOT NULL COLLATE 'utf8_general_ci',
            `params` TEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
            `api_key` VARCHAR(40) NOT NULL COLLATE 'utf8_general_ci',
            `ip_address` VARCHAR(45) NOT NULL COLLATE 'utf8_general_ci',
            `duration` FLOAT NULL DEFAULT NULL,
            `authorized` VARCHAR(1) NOT NULL COLLATE 'utf8_general_ci',
            `response_code` SMALLINT(3) NULL DEFAULT '0',
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `deleted_at` DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (`id`) USING BTREE,
            INDEX `deleted_at` (`deleted_at`) USING BTREE
        )
        COLLATE='utf8_general_ci'
        ENGINE=InnoDB
        AUTO_INCREMENT=1
    |
    */
    public $restEnableLogging = true;

    /*
    |--------------------------------------------------------------------------
    | REST API Logs Table Name
    |--------------------------------------------------------------------------
    |
    | If not using the default table schema in 'restEnableLogging', specify the
    | table name to match e.g. my_logs
    |
    */
    public $configRestLogsTable = 'logs';

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

    /*
    |--------------------------------------------------------------------------
    | CORS Forced Headers
    |--------------------------------------------------------------------------
    |
    | If using CORS checks, always include the headers and values specified here
    | in the OPTIONS client preflight.
    | Example:
    | $config['forced_cors_headers'] = [
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
