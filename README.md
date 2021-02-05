# Doctrine

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


## Usage Loading Library

```php
<?php namespace App\Controllers;

class Center extends \Daycry\RestServer\RestServer
{
    public function index()
    {
        try
		{
            if( $this->checkRequest() !== true )
			{
				return $this->checkRequest();
			}

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
            if( $this->checkRequest() !== true )
			{
				return $this->checkRequest();
			}

            return $this->respond( $this->encryption->encrypt('data') );

        }catch ( \Exception $e )
		{
            return $this->fail( $e->getMessage() );
        }
    }
}

```


