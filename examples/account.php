<?php

// Loads the class
require '../udger.php';

// creates a new UdgerParser object
$parser = new Udger\Parser(true); // Development/Debug
//$parser = new Udger\Parser(); // Production
//
// set You Acceskey (see https://udger.com/account/main) 
$parser->SetAccessKey('XXXXXX');

$ret = $parser->account();
echo "<pre>";
print_r($ret);
echo "</pre>";
?>