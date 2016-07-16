<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

// creates a new UdgerParser object
$factory = new Udger\ParserFactory(sys_get_temp_dir() . "/udgercache/udgerdb_v3.dat");
$parser = $factory->getParser();

try {
    $parser->setUA('Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.97 Safari/537.36');
    $parser->setIP("66.249.64.1");

    $ret = $parser->parse();
    var_dump($ret);
} catch (Exception $ex) {
    echo "Error: " . $ex->getMessage(). PHP_EOL;
}
