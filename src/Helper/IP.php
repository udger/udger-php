<?php


namespace Udger\Helper;

/**
 * IP address helper
 *
 * @author tiborb
 */
class IP implements IPInterface{

    /**
     * Get IP verison
     * 
     * @param string $ip
     * @return integer|boolean Returns version or false on invalid address
     */
    public function getIpVersion($ip)
    {
        if (false !== filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return self::IPv6;
        }

        if (false !== filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return self::IPv4;
        }
        // invalid ip
        return false;
    }
    
    /**
     * Ip to long
     * 
     * @param string $ip
     * @return integer
     */
    public function getIpLong($ip)
    {
        return sprintf('%u', ip2long($ip));
    }
}