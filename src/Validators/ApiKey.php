<?php

namespace Daycry\RestServer\Validators;

use CodeIgniter\HTTP\RequestInterface;
use Daycry\RestServer\Exceptions\UnauthorizedException;

class ApiKey
{
    use \Daycry\RestServer\Traits\Schema;

    public static function check(RequestInterface $request, object $petition = null, array $args, bool &$authorized): ?object
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

                if (!($row = $keyModel->setSchema(self::getSchema())->with(config('RestServer')->restUsersTable)->where(config('RestServer')->restKeyColumn, $key)->first())) {
                    $authorized = false;
                    throw UnauthorizedException::forInvalidApiKey($key);
                }

                $row = \Daycry\RestServer\Libraries\Utils::modelAliases($row, config('RestServer')->restUsersTable, 'user');

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
                            if (strpos($list_ip, '/') !== false) {
                                //check IP is in the range
                                $found_address = \Daycry\RestServer\Libraries\CheckIp::ipv4_in_range(trim($list_ip), $row->ip_addresses);
                            } elseif ($ip_address === trim($list_ip)) {
                                // there is a match, set the the value to TRUE and break out of the loop
                                $found_address = true;
                                break;
                            }
                        }

                        if (!$found_address) {
                            $authorized = false;
                            throw UnauthorizedException::forIpDenied();
                        }
                    } else {
                        $authorized = false;
                        throw UnauthorizedException::forIpDenied();
                    }
                }
            } else {
                $authorized = false;
                throw UnauthorizedException::forInvalidApiKey($key);
            }
        }

        return $row;
    }
}
