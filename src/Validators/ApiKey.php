<?php

namespace Daycry\RestServer\Validators;

use CodeIgniter\HTTP\RequestInterface;
use Daycry\RestServer\Exceptions\UnauthorizedException;

class ApiKey
{
    public static function check(RequestInterface $request, array $args, object $petition = null, string &$key = null): ?object
    {
        $row = null;
        $usekey = config('RestServer')->restEnableKeys;

        if ($petition) {
            if (isset($petition->key)) {
                $usekey = ($petition->key === null || $petition->key == 1) ? config('RestServer')->restEnableKeys : false;
            }
        }

        if ($usekey) {
            if (($key = isset($args[ config('RestServer')->restKeyName ]) ? $args[ config('RestServer')->restKeyName ] : $request->getHeaderLine(\strtolower(config('RestServer')->restKeyName)))) {
                $keyModel = new \Daycry\RestServer\Models\KeyModel();

                if (!($row = $keyModel->where(config('RestServer')->restKeyColumn, $key)->first())) {
                    return UnauthorizedException::forInvalidApiKey($key);
                }

                $row->{config('RestServer')->restUsersTable} = $row->{config('RestServer')->restUsersTable};

                /**
                 * If "is private key" is enabled, compare the ip address with the list
                 * of valid ip addresses stored in the database
                 */
                if (empty($row->is_private_key) === false) {
                    $found_address = false;
                    // Check for a list of valid ip addresses
                    if (isset($row->ip_addresses)) {
                        $ip_address = $request->getIPAddress();

                        // multiple ip addresses must be separated using a comma, explode and loop
                        $list_ip_addresses = explode(',', $row->ip_addresses);

                        foreach ($list_ip_addresses as $list_ip) {
                            if ($list_ip) {
                                if (strpos($list_ip, '/') !== false) {
                                    //check IP is in the range
                                    $found_address = \Daycry\RestServer\Libraries\CheckIp::ipv4_in_range(trim($ip_address), trim($list_ip));
                                } elseif ($ip_address === trim($list_ip)) {
                                    // there is a match, set the the value to TRUE and break out of the loop
                                    $found_address = true;
                                    break;
                                }
                            }
                        }

                        if ($found_address !== true) {
                            return UnauthorizedException::forIpDenied();
                        }
                    } else {
                        return UnauthorizedException::forIpDenied();
                    }
                }
            } else {
                return UnauthorizedException::forInvalidApiKey($key);
            }
        }

        return ($row) ? (object)$row->jsonSerialize() : null;
    }
}
