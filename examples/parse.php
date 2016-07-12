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

//If you want information about fragments
$parser->setParseFragments(true);


try {
    $useragent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.71 Safari/537.36';
    // Gets information about the current user agent
    $ret = $parser->parse($useragent);
    var_dump($ret);
} catch (Exception $ex) {
    echo "Error: " . $ex->getMessage(). PHP_EOL;
}