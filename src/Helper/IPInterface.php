<?php

namespace Udger\Helper;

/**
 *
 * @author tiborb
 */
interface IPInterface {

    const IPv4 = 4;
    const IPv6 = 6;

    public function getIpVersion($ip);
    
    public function getIpLong($ip);
    
    public function getIp6array($ip);
}
