<?php
namespace Daycry\RestServer\Libraries\Auth;

class DigestAuth extends BaseAuth implements AuthInterface
{
    public function __construct()
    {
        $this->method = 'digest';
        parent::__construct();
    }

    public function validate()
    {
        $digest_string = $this->request->getServer( 'PHP_AUTH_DIGEST' );
        if( $digest_string === null )
        {
            $digest_string = $this->request->getServer( 'HTTP_AUTHORIZATION' );
        }

        $unique_id = uniqid();

        // The $_SESSION['error_prompted'] variable is used to ask the password
        // again if none given or if the user enters wrong auth information
        if( empty( $digest_string ) )
        {
            $this->forceLogin( $unique_id );
        }

        // We need to retrieve authentication data from the $digest_string variable
        $matches = [];
        preg_match_all( '@(username|nonce|uri|nc|cnonce|qop|response)=[\'"]?([^\'",]+)@', $digest_string, $matches );
        $digest = ( empty( $matches[1] ) || empty( $matches[2] ) ) ? [] : array_combine( $matches[1], $matches[2] );
        
        //Workaround for access atributes
        $username = $uri = $nonce = $nc = $cnonce = $qop = $response = false;
        foreach( $digest as $key => $value ){ ${$key} = $digest[ $key ]; }

        // For digest authentication the library function should return already stored md5(username:restrealm:password) for that username see rest.php::auth_library_function config
        $usernameMd5 = $this->checkLogin( $username, true );
        
        if( $usernameMd5 instanceof \Exception )
        {
            return $usernameMd5;
        }

        if( array_key_exists( 'username', $digest ) === false || $usernameMd5 === false )
        {
            $this->forceLogin( $unique_id );
        }

        $md5 = md5( strtoupper( $this->request->getMethod() ).':'.$uri );
        $valid_response = md5( $usernameMd5 . ':' . $nonce . ':' . $nc . ':' . $cnonce . ':'.$qop . ':' . $md5 );

        // Check if the string don't compare (case-insensitive)
        if( \strcasecmp( $response, $valid_response ) !== 0 )
        {
            $this->isValidRequest = false;
            return false;
        }

        return $username;
    }
}