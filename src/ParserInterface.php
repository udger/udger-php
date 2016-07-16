<?php

namespace Udger;

/**
 *
 * @author tiborb
 */
interface ParserInterface {
    
    public function account();
    
    public function parse();
    
    public function setUA($ua);
    
    public function setIP($ip);
    
    public function setAccessKey($access_key);
    
    public function setDataFile($path);
}