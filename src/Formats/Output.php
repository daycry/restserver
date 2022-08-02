<?php

namespace Daycry\RestServer\Formats;

use CodeIgniter\HTTP\RequestInterface;

class Output
{
    public static function check(RequestInterface $request, array $args): string
    {
        $format = self::_getFormatInArgs($args);

        $mimes = \Config\Mimes::$mimes;

        if( $format ) {
            $response = 'application/json';
            $response = \Config\Mimes::guessTypeFromExtension($format);

            if(!$response)
            {
                return $request->negotiate('media', config('Format')->supportedResponseFormats);
            }

            return $response;
        }else{
            return $request->negotiate('media', config('Format')->supportedResponseFormats);
        }
    }

    private static function _getFormatInArgs( array $args) :?string
    {
        $format = null;
        foreach( $args as $key => $value )
        {
            if( $key === 'format' )
            {
                $format = $value;
                break;
            }
        }

        return $format;
    }
}
