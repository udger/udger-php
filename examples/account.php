<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

// creates a new UdgerParser object
$parser = new Udger\Parser(true); // Development/Debug
//$parser = new Udger\Parser(); // Production
//
// set You Acceskey (see https://udger.com/account/main) 
$parser->setAccessKey('XXXXXX');

$ret = $parser->account();
echo "<pre>";
print_r($ret);
echo "</pre>";
?>