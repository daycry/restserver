<?php
namespace Daycry\RestServer\Libraries\Auth;

use Daycry\RestServer\Interfaces\AuthInterface;

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

        // protect against missing data
        $needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
        $data = array();
        preg_match_all( '@(\w+)=(?:(?:\'([^\']+)\'|"([^"]+)")|([^\s,]+))@', $digest_string, $matches, PREG_SET_ORDER );

        foreach( $matches as $m )
        {
            $data[$m[1]] = $m[2] ? $m[2] : ($m[3] ? $m[3] : $m[4]);
            unset($needed_parts[$m[1]]);
        }

        if( $needed_parts )
        {
            $this->forceLogin( $unique_id );
        }

        $username = $nonce = $nc = $cnonce = $qop = $username = $uri = $response = null;
        foreach( $data as $key => $value ){ ${ $key } = $value; }
        
        // For digest authentication the library function should return already stored md5(username:restrealm:password) for that username see rest.php::auth_library_function config
        $usernameMD5 = $this->checkLogin( $username, true );
        
        if( $username === false || $usernameMD5 === false )
        {
            $this->forceLogin( $unique_id );
        }

        $md5 = md5( strtoupper( $this->request->getMethod() ).':'. $uri );

        $valid_response = md5( $usernameMD5 . ':' . $nonce . ':' . $nc . ':' . $cnonce . ':'. $qop . ':' . $md5 );

        // Check if the string don't compare (case-insensitive)
        if( \strcasecmp( $response, $valid_response ) !== 0 )
        {
            $this->isValidRequest = false;
            return false;
        }

        return $username;
    }
}