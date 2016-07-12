<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

// creates a new UdgerParser object
$parser = new Udger\Parser(true); // Development/Debug
//$parser = new Udger\Parser(); // Production
//
// set You Acceskey (see https://udger.com/account/main) 
$parser->setAccessKey('XXXXXX');

try {
    $ret = $parser->account();
    var_dump($ret);
} catch (Exception $ex) {
    echo "Error: " . $ex->getMessage() . PHP_EOL;
}