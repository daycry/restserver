# Rest Server

Rest Server with Doctrine for Codeigniter 4

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

This command will copy a config file to your app namespace
Then you can adjust it to your needs. By default file will be present in `app/Config/RestServer.php`.

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

