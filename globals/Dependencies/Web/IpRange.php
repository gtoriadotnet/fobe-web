<?php

/**
 * Copyright 2015-2021 Nikita Petko (http://two-time-corp.mfdlabs.local/ui/?petko/)
 */


/*
Example:

<?php

use Alphaland\Web\IpHelper;

require_once './globals/Dependencies/Web/IpRange.php';

$baseAddress = '127.0.0.1';
$cidrNotation = '127.0.0.0/8'; // 127.0.0.0-127.255.255.255 or 127.0.0.0/255.255.255.0
$ipRange = '127.0.0.0-127.255.255.255'; // 127.0.0.0/255.255.255.0 or 127.0.0.0/8
$netmask = '127.0.0.0/255.255.255.0'; // 127.0.0.0-127.255.255.255 or 127.0.0.0/8

assert(IpHelper::IsIpInCidrRange($baseAddress, $cidrNotation));
assert(IpHelper::IsIpInRange($baseAddress, $ipRange));
assert(IpHelper::IsIpInNetmask($baseAddress, $netmask));

*/

namespace Alphaland\Web {
    /**
     * A class to help in the aid of IP address identification.
     */
    class IpHelper
    {
        /**
         * Is the given IP address a valid IPv4 address?
         * 
         * @param string $ip IP address
         * 
         * @return bool true if IP is an IPv4 address
         */
        public static function IsIpv4(string $ip)
        {
            return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
        }

        /**
         * Is the given IP address a valid IPv6 address?
         * 
         * @param string $ip IP address
         * 
         * @return bool true if IP is an IPv6 address
         */
        public static function IsIpv6(string $ip)
        {
            return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
        }

        /**
         * Is the given IP address a valid IPv4 or IPv6 address?
         * 
         * @param string $ip IP address
         * 
         * @return bool true if IP is an IPv4 or IPv6 address
         */
        public static function IsIp(string $ip)
        {
            return self::IsIpv4($ip) || self::IsIpv6($ip);
        }

        /**
         * Determines if the given ip is in the IP range notation like below:
         * 
         * $ip = "127.0.0.1"
         * $range = "127.0.0.0-127.255.255.255"
         * 
         * $isInRange = IpHelper::IsIpInRange($ip, $range); // true
         * 
         * @param string $ip IP address
         * @param string $range IP range
         * 
         * @return bool true if IP is in the range
         */
        public static function IsIpInRange(string $ip, string $range)
        {
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

        /**
         * Determines if the given address is in the list of IP range notations.
         * 
         * @param string $ip IP address
         * @param array $ranges IP ranges
         * 
         * @return bool true if IP is in any of the ranges
         */
        public static function IsIpInRangeList(string $ip, array $ranges)
        {
            foreach ($ranges as $range) {
                if (self::IsIpInRange($ip, $range)) {
                    return true;
                }
            }

            return false;
        }

        /**
         * Determine if the given IP is in the Netmask notation like below:
         * 
         * $ip = "127.0.0.1"
         * $netmask = "127.0.0.0/255.255.255.0"
         * 
         * $isInNetmask = IpHelper::IsIpInNetmask($ip, $netmask); // true
         * 
         * @param string $ip IP address
         * @param string $netmask Netmask
         * 
         * @return bool true if IP is in the netmask
         */
        public static function IsIpInNetmask(string $ip, string $netmask)
        {
            if (strpos($netmask, '/') !== false) {
                list($range, $netmask) = explode('/', $netmask, 2);

                if (strpos($netmask, '.') !== false) {
                    // $netmask is a
                    $netmask = str_replace('*', '0', $netmask);
                    $netmask_dec = ip2long($netmask);
                    return ((ip2long($ip) & $netmask_dec) == (ip2long($range) & $netmask_dec));
                }
            }

            return false;
        }

        /**
         * Determines if the given address is in the list of Netmask notations.
         * 
         * @param string $ip IP address
         * @param array $netmasks Netmasks
         * 
         * @return bool true if IP is in any of the netmasks
         */
        public static function IsIpInNetmaskList(string $ip, array $netmasks)
        {
            foreach ($netmasks as $netmask) {
                if (self::IsIpInNetmask($ip, $netmask)) {
                    return true;
                }
            }

            return false;
        }

        /**
         * Determines if the given IP is in the CIDR notation like below:
         * 
         * $ip = "127.0.0.1"
         * $cidr = "127.0.0.0/8"
         * 
         * $isInCidr = IpHelper::IsIpInCidrRange($ip, $cidr); // true
         * 
         * @param string $ip IP address
         * @param string $cidr CIDR
         * @return bool true if IP is in the CIDR
         */
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

        /**
         * Determine if the given IP is in the CIDR notation list.
         * 
         * @param string $ip IP address
         * @param array $cidrs CIDRs
         * 
         * @return bool true if IP is in any of the CIDRs
         */
        public static function IsIpInCidrRangeList(string $ip, array $cidrs)
        {
            foreach ($cidrs as $cidr) {
                if (self::IsIpInCidrRange($ip, $cidr)) {
                    return true;
                }
            }

            return false;
        }

        /**
         * Determines if the given IP is in the given CIDR, Range or Netmask list
         * 
         * @param string $ip IP address
         * @param string $cidr CIDR, Range or Netmask
         * 
         * @return bool true if IP is in the any of the CIDRs, Ranges or Netmasks
         */
        public static function IsIpInCidrNetmaskOrRangeList(string $ip, array $cidrs)
        {
            foreach ($cidrs as $cidr) {
                if (self::IsIpInCidrRange($ip, $cidr) || self::IsIpInNetmask($ip, $cidr) || self::IsIpInRange($ip, $cidr) || $ip === $cidr) {
                    return true;
                }
            }

            return false;
        }
    }
}
