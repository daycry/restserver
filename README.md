[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.com/donate?business=SYC5XDT23UZ5G&no_recurring=0&item_name=Thank+you%21&currency_code=EUR)

# Rest Server

Rest Server with Doctrine for Codeigniter 4

[![Build Status](https://github.com/daycry/restserver/workflows/PHP%20Tests/badge.svg)](https://github.com/daycry/restserver/actions?query=workflow%3A%22PHP+Tests%22)
[![Coverage Status](https://coveralls.io/repos/github/daycry/restserver/badge.svg?branch=master)](https://coveralls.io/github/daycry/restserver?branch=master)
[![Downloads](https://poser.pugx.org/daycry/restserver/downloads)](https://packagist.org/packages/daycry/restserver)
[![GitHub release (latest by date)](https://img.shields.io/github/v/release/daycry/restserver)](https://packagist.org/packages/daycry/restserver)
[![GitHub stars](https://img.shields.io/github/stars/daycry/restserver)](https://packagist.org/packages/daycry/restserver)
[![GitHub license](https://img.shields.io/github/license/daycry/restserver)](https://github.com/daycry/restserver/blob/master/LICENSE)

## **Version 7**

## This version breaks compatibility with previous versions, since it contains new automated features, restructuring of the database with new foreign keys, so if you update, check that the names of the tables are in the plural.

## Installation via composer

Use the package with composer install

	> composer require daycry/restserver

## Manual installation

Download this repo and then enable it by editing **app/Config/Autoload.php** and adding the **Daycry\RestServer**
namespace to the **$psr4** array. For example, if you copied it into **app/ThirdParty**:

```php
$psr4 = [
    'Config'      => APPPATH . 'Config',
    APP_NAMESPACE => APPPATH,
    'App'         => APPPATH,
    'Daycry\RestServer' => APPPATH .'ThirdParty/restserver/src',
];
```

## Configuration

Run command:

    > php spark restserver:publish
    > php spark settings:publish
    > php spark cronjob:publish
    > php spark jwt:publish

This command will copy a config file to your app namespace.
Then you can adjust it to your needs. By default file will be present in `app/Config/RestServer.php`.

    > php spark migrate -all

This command create rest server tables in your database.

If you want load and Example Seed you can use this command.

    > php spark db:seed Daycry\RestServer\Database\Seeds\ExampleSeeder

More information about install doctrine: https://github.com/daycry/doctrine

## Usage Loading Library

```php
<?php namespace App\Controllers;

class Center extends \Daycry\RestServer\RestServer
{
    public function index()
    {
        return $this->respond( $this->content );
    }
}

```
If you want change attributes before controller call:

```php
<?php namespace App\Controllers;

class Center extends \Daycry\RestServer\RestServer
{
    public function __construct()
    {
        $this->_restConfig = config('RestServer');
        $this->_restConfig->restAjaxOnly = true;
    }

    public function index()
    {
        return $this->respond( $this->content );
    }
}

```

If you need to validate the data, you can call `validation` method passing the string rules and Validation Config file y you need.

For Example: `app/Config/Validation.php` or if rules are in custom namespace `app/Modules/Example/Config/Validation.php`

```php
	public $requiredLogin = [
		'username'		=> 'required',
		'password'		=> 'required',
		//'fields'		=> 'required'
	];
```

```php
<?php namespace App\Controllers;

class Center extends \Daycry\RestServer\RestServer
{
    public function index()
    {
        $this->validation( 'requiredLogin' );
        return $this->respond( $this->content );
    }
}
```

**$this->content** contains a body content in the request.
**$this->args** contains all params, get, post, headers,... but you can use the object **$this->request** for get this params if you want.

```php
<?php namespace App\Controllers;

class Center extends \Daycry\RestServer\RestServer
{
    public function index()
    {
        $this->validation( 'requiredLogin', config( Example\\Validation ), true, true );
        return $this->respond( $this->content );
    }
}
```
Validation function parameters

Fiels | Desctiption
-------- | -----------
rule | Name of rule
namespace| Namespace that contains the rule
getShared | **true** or **false**
filter | If you want to limit the content of the body, exclusively to the parameters of the rule.


## User Model Class

By default you can associate users with keys via the '\Daycry\RestServer\Models\UserModel' model, but you can customize it by creating an existing class of '\Daycry\RestServer\Libraries\User\UserAbstract'.

Example:

```php
<?php
namespace App\Models;

use Daycry\RestServer\Libraries\User\UserAbstract;

class CustomUserModel extends UserAbstract
{
    protected $DBGroup = 'default';

    protected $table      = 'users';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'object';

    protected $useSoftDeletes = true;

    protected $allowedFields = [ 'name', 'key_id' ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;
}
```

If you customize the user class, you have to modify the default configuration

Example:

```php
<?php
    public $restUsersTable = 'restserver_user'; //user table name
    public $userModelClass = \Daycry\RestServer\Models\UserModel::class; //user model Class
    public $userKeyColumn = 'key_id'; // column that associates the key 
```

## Exceptions & block Invalid Attempts

If you want to use some custom exception to use it as a failed request attempt and allow the blocking of that IP, you have to create the static attribute **authorized**.

If **authorized** is **false** the system increases by 1 the failed attempts by that IP.
Example:

```php
<?php

    namespace App\Exceptions;

    use CodeIgniter\Exceptions\FrameworkException;

    class SecretException extends FrameworkException
    {
        protected $code = 401;

        public static $authorized = true;

        public static function forInvalidPassphrase()
        {
            self::$authorized = false;
            return new self(lang('Secret.invalidPassphrase'));
        }

        public static function forInvalidToken()
        {
            self::$authorized = false;
            return new self(lang('Secret.invalidToken'));
        }

        public static function forExpiredToken()
        {
            self::$authorized = false;
            return new self(lang('Secret.tokenExpired'));
        }

        public static function forTokenReaded()
        {
            self::$authorized = false;
            return new self(lang('Secret.readed'));
        }
    }
```

## OPTIONS

You can customize the requests independently using the `petition` table.

Fiels | Value | Options | Desctiption
-------- | ------------- | ------- | -----------
`namespace_id` | | | This field contains the identifier of the **ws_namespaces** table, this table stores the namespaces of the classes, for example `\App\Controllers\Auth`
`method`| `login` | | Use this field to configure the method of controller
`http`| `post` | `get`,`post`,`put`,`patch`, `options` | Use this field to configure the method of controller
`auth`| `bearer` | `false`,`basic`,`digest`,`bearer` | Use this field to configure the autentication method
`log`| `null` | `null`,`1`,`0` | Use this field if you want log the petition
`limit`| `null` | `null`,`1`,`15` | Use this field if you want to set a request limit, this value must be an integer
`time`| `null` | `null`,`1`,`15` | This field is used to know how often the request limit is reset ( Time in seconds Example: 3600 -> In this case you can do {limit} request in 3600 seconds)
`level`| `null` | `null`,`1`,`10` | Use this field to indicate the level of permissions in the request, if the token has level 1 and the request has level 3, you will not be able to make the request

You can fill the **ws_namespaces** automatically with a command.

```php
<?php

    php spark restserver:discover
```

This command search class in the namespace o namespaces that you want.
You can set this namespaces in the **RestServer.php** config file.

```php
<?php

    /**
    *--------------------------------------------------------------------------
    * Cronjob
    *--------------------------------------------------------------------------
    *
    * Set to TRUE to enable Cronjob for fill the table petitions with your API classes
    * $restNamespaceScope \Namespace\Class or \Namespace\Folder\Class or \Namespace example: \App\Controllers
    *
    * This feature use Daycry\CronJob vendor
    * for more information: https://github.com/daycry/cronjob
    *
    */
    public string $restApiTable = 'ws_apis';
    public string $restNamespaceTable = 'ws_namespaces';
    public array $restNamespaceScope = ['\App\Controllers', '\Api\Controllers'];
```

Or creating a cronjob tasks editing **CronJob.php** config file like this.

```php
<?php

    /*
    |--------------------------------------------------------------------------
    | Cronjobs
    |--------------------------------------------------------------------------
    |
    | Register any tasks within this method for the application.
    | Called by the TaskRunner.
    |
    | @param Scheduler $schedule
    */
    public function init(Scheduler $schedule)
    {
        $schedule->command('restserver:discover')->named('discoverRestserver')->dayly();
        or
        $schedule->command('restserver:discover')->named('discoverRestserver')->dayly('11:30 am');
    }
```

More information about cronjob: https://github.com/daycry/cronjob

## RESPONSE

The default response is in `json` but you can change to `xml` in the headers.

```
Accept: application/json
```
or
```
Accept: application/xml
```
or you can set format in GET variable
```
http://example.com?format=json
http://example.com?format=xml
```

## INPUT BODY

The body of petition is `json` by default, but you can change it.

```
Content-Type: application/json
```
or
```
Content-Type: application/xml
```


## API TOKEN

You can sent `api rest token` in headers, GET or POST variable like this.
```
X-API-KEY: TOKEN
```
```
http://example.com?X-API-KEY=key
```

## LANGUAGE

You can sent `language` like this.
```
Accept-Language: en
```

