<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

$factory = new Udger\ParserFactory();
$parser = $factory->getParser();

try {
    // set You Acceskey (see https://udger.com/account/main) 
    $parser->setAccessKey('XXXXXXXX');
    $ret = $parser->account();
    var_dump($ret);
} catch (Exception $ex) {
    echo "Error: " . $ex->getMessage() . PHP_EOL;
}