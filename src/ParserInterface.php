<?php

namespace Udger;

/**
 *
 * @author tiborb
 */
interface ParserInterface {
    
    public function account();
    
    public function parse($useragent);
    
    public function setAccessKey($access_key);
    
    public function setAutoUpdate($value);
    
    public function setDataDir($data_dir);
            
    public function updateData();
}