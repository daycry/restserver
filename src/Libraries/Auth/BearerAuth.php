<?php
namespace Daycry\RestServer\Libraries\Auth;

class BearerAuth extends BaseAuth implements AuthInterface
{
    public function __construct()
    {
        $this->method = 'bearer';
        parent::__construct();
    }

    public function validate()
    {
        // Returns HTTP_AUTHENTICATION don't exist
        $http_auth = $this->request->getServer( 'HTTP_AUTHENTICATION' ) ?: $this->request->getServer( 'HTTP_AUTHORIZATION' );

        $username = null;
        if( $http_auth !== null )
        {
            // If the authentication header is set as bearer, then extract the token from
            if( strpos( strtolower( $http_auth ), 'bearer' ) === 0 ) 
            {
                $username = substr( $http_auth, 7 );
            }
        }

        $username = $this->checkLogin( $username, true );

        if( $username instanceof \Exception )
        {
            return $username;
        }

        if( $username === false )
        {
            $this->forceLogin();
        }

        return $username;
    }
}