<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

// creates a new UdgerParser object
$factory = new Udger\ParserFactory();
$parser = $factory->getParser();

// set data dir (this php script must right write to cache dir)
$parser->setDataDir(sys_get_temp_dir() . "/udgercache/");

// set You Acceskey (see https://udger.com/account/main) 
//$parser->setAccessKey('XXXXXX'); 
// or download the data manually from http://data.udger.com/[ACCESS_KEY]/udgerdb.dat


//method "parse"
$parser->setUA('Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.97 Safari/537.36');
$parser->setIP("66.249.64.1");

// Gets information about the current user agent
$ret = $parser->parse();
echo "<pre>";
print_r($ret);
echo "</pre>\n";


//method "account"
$ret = $parser->account();
echo "<pre>";
print_r($ret);
echo "</pre>\n";

// Update agents data
$ret = $parser->updateData();

?>
