[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.com/donate?business=SYC5XDT23UZ5G&no_recurring=0&item_name=Thank+you%21&currency_code=EUR)

# Rest Server

Rest Server with Doctrine for Codeigniter 4

## UPDATE AVAILABLE V3

This update is not compatible with the previous version and greatly improves error handling.

[Readme](https://github.com/daycry/restserver/blob/master/UPDATE-v3.md)


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

This command will copy a config file to your app namespace
Then you can adjust it to your needs. By default file will be present in `app/Config/RestServer.php`.

    > php spark migrate -all

This command create rest server tables in your database.

More information about install doctrine: https://github.com/daycry/doctrine

## VERSION 3

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

```php
<?php namespace App\Controllers;

class Center extends \Daycry\RestServer\RestServer
{
    public function index()
    {
        $this->validation( 'requiredLogin', config( Example\\Validation ) );
        return $this->respond( $this->content );
    }
}
```

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
_________________________________________________________________________________________

## VERSION 2

## Usage Loading Library

```php
<?php namespace App\Controllers;

class Center extends \Daycry\RestServer\RestServer
{
    public function index()
    {
        try
		{
            $validation = $this->checkRequest( null );
            //if( $validation !== true ){ return $validation; }

            return $this->respond( $this->content );

        }catch ( \Exception $e )
		{
            return $this->fail( $e->getMessage() );
        }
    }

    public function encrypt( $id = null, $petitionId = null )
    {
        try
		{
            $validation = $this->checkRequest( null );
            if( $validation !== true ){ return $validation; }

            return $this->respond( $this->encryption->encrypt('data') );

        }catch ( \Exception $e )
		{
            return $this->fail( $e->getMessage() );
        }
    }
}

```
You can pass validation group rules in `checkRequest` function as a string.

In `app/Config/Validation.php`
```php
	public $requiredLogin = [
		'username'		=> 'required',
		'password'		=> 'required',
		//'fields'		=> 'required'
	];
```
```
$validation = $this->checkRequest( 'requiredLogin' );
```
____________________________________________________________________________________________

## OPTIONS

You can customize the requests independently using the `petition` table.

Fiels | Value | Options | Desctiption
-------- | ------------- | ------- | -----------
`controller` | `\App\Controllers\Auth` | | Use this field to configure the controller in namespace format
`method`| `login` | | Use this field to configure the method of controller
`http`| `post` | `get`,`post`,`put`,`patch`, `options` | Use this field to configure the method of controller
`auth`| `bearer` | `false`,`basic`,`digest`,`bearer` | Use this field to configure the autentication method
`log`| `null` | `null`,`1`,`0` | Use this field if you want log the petition
`limit`| `null` | `null`,`1`,`15` | Use this field if you want to set a request limit, this value must be an integer
`time`| `null` | `null`,`1`,`15` | This field is used to know how often the request limit is reset
`level`| `null` | `null`,`1`,`10` | Use this field to indicate the level of permissions in the request, if the token has level 1 and the request has level 3, you will not be able to make the request

## RESPONSE

The default response is in `json` but you can change to `xml` in the headers.

```
Accept: application/json
```
or
```
Accept: application/xml
```

## INPUT BODY

The body of petition is `json` by defult, but you can change it.

```
Content-Type: application/json
```
or
```
Content-Type: application/xml
```

## API TOKEN

You can sent `api rest token` like this.
```
X-API-KEY: application/json
```

## LANGUAGE

You can sent `api rest token` like this.
```
Accept-Language: en
```

