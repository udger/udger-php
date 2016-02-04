<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

// creates a new UdgerParser object
$parser = new Udger\Parser(true); // Development/Debug
//$parser = new Udger\Parser(); // Production

// Disable automatic updates
$parser->setAutoUpdate(false);

// set data dir (this php script must right write to cache dir)
$parser->setDataDir(sys_get_temp_dir() . "/udgercache/");

// set You Acceskey (see https://udger.com/account/main) 
$parser->setAccessKey('XXXXXX'); 
// or download the data manually from http://data.udger.com/[ACCESS_KEY]/udgerdb.dat

// Update agents data
$ret = $parser->updateData();

echo "<pre>";
print_r($ret);
echo "</pre>\n";

?>