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

        else if (false !== filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
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
    
    
    /**
     * Ipv6 to array
     * 
     * @param string $ip
     * @return array
     */
    public function getIp6array($ip){
      // expand - example: "2600:3c00::" ->  "2600:3c00:0000:0000:0000:0000:0000:0000"
      $hex = unpack("H*hex", inet_pton($ip));         
      $ipStr = substr(preg_replace("/([A-f0-9]{4})/", "$1:", $hex['hex']), 0, -1);
      
      $ipIntArray = array();
      $ipStrArray = explode(":", $ipStr);
      
      foreach ($ipStrArray as &$value) {
        $ipIntArray[] = hexdec($value);
      }
      
      return $ipIntArray;
    }
}
