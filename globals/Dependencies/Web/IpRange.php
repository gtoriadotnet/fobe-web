<?php

/**
 * Copyright 2015-2021 Nikita Petko (http://two-time-corp.mfdlabs.local/ui/?petko/)
 */

namespace Alphaland\Web {
    class IpHelper
    {
        public static function IsIpv4(string $ip)
        {
            return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
        }

        public static function IsIpv6(string $ip)
        {
            return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
        }

        public static function IsIp(string $ip)
        {
            return self::IsIpv4($ip) || self::IsIpv6($ip);
        }

        public static function IpIsInRange(string $ip, string $range)
        {
            if (strpos($range, '/') !== false) {
                // $range is in IP/NETMASK format
                list($range, $netmask) = explode('/', $range, 2);
                if (strpos($netmask, '.') !== false) {
                    // $netmask is a 255.255.0.0 format
                    $netmask = str_replace('*', '0', $netmask);
                    $netmask_dec = ip2long($netmask);
                    return ((ip2long($ip) & $netmask_dec) == (ip2long($range) & $netmask_dec));
                } else {
                    // $netmask is a CIDR size block
                    // fix the range argument
                    $x = explode('.', $range);
                    while (count($x) < 4) $x[] = '0';
                    list($a, $b, $c, $d) = $x;
                    $range = sprintf("%u.%u.%u.%u", empty($a) ? '0' : $a, empty($b) ? '0' : $b, empty($c) ? '0' : $c, empty($d) ? '0' : $d);
                    $range_dec = ip2long($range);
                    $ip_dec = ip2long($ip);

                    # Strategy 1 - Create the netmask with 'netmask' 1s and then fill it to 32 with 0s
                    #$netmask_dec = bindec(str_pad('', $netmask, '1') . str_pad('', 32-$netmask, '0'));

                    # Strategy 2 - Use math to create it
                    $wildcard_dec = pow(2, (32 - $netmask)) - 1;
                    $netmask_dec = ~$wildcard_dec;

                    return (($ip_dec & $netmask_dec) == ($range_dec & $netmask_dec));
                }
            } else {
                // range might be 255.255.*.* or 1.2.3.0-1.2.3.255
                if (strpos($range, '*') !== false) { // a.b.*.* format
                    // Just convert to A-B format by setting * to 0 for A and 255 for B
                    $lower = str_replace('*', '0', $range);
                    $upper = str_replace('*', '255', $range);
                    $range = "$lower-$upper";
                }

                if (strpos($range, '-') !== false) { // A-B format
                    list($lower, $upper) = explode('-', $range, 2);
                    $lower_dec = (float)sprintf("%u", ip2long($lower));
                    $upper_dec = (float)sprintf("%u", ip2long($upper));
                    $ip_dec = (float)sprintf("%u", ip2long($ip));
                    return (($ip_dec >= $lower_dec) && ($ip_dec <= $upper_dec));
                }

                return false;
            }
        }

        public static function IsIpInCidrRange(string $ip, string $cidr)
        {
            list($subnet, $bits) = explode('/', $cidr);
            if ($bits === null) {
                $bits = 32;
            }
            $ip = ip2long($ip);
            $subnet = ip2long($subnet);
            $mask = -1 << (32 - $bits);
            $subnet &= $mask; # nb: in case the supplied subnet wasn't correctly aligned
            return ($ip & $mask) == $subnet;
        }
    }
}
