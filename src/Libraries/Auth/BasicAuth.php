<?php
namespace Daycry\RestServer\Libraries\Auth;

class BasicAuth extends BaseAuth implements AuthInterface
{
    public function __construct()
    {
        parent::__construct();
    }

    public function validate()
    {
        $username = $this->request->getServer( 'PHP_AUTH_USER' );
        $http_auth = $this->request->getServer( 'HTTP_AUTHENTICATION' ) ?: $this->request->getServer( 'HTTP_AUTHORIZATION' );

        $password = NULL;
        if ($username !== NULL)
        {
            $password = $this->request->getServer( 'PHP_AUTH_PW' );

        }elseif ($http_auth !== NULL)
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
        $username = $this->checkLogin( $username, $password );

        if( $username instanceof \Exception )
        {
            return $username;
        }

        if( $username === false )
        {
            $this->forceLogin();
            return false;
        }

        return $username;

    }
}