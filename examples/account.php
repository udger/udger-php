<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

$factory = new Udger\ParserFactory();
$parser = $factory->getParser();

// set You Acceskey (see https://udger.com/account/main) 
$parser->setAccessKey('94a4d5510a30ef2e367b27761ebc765b');

try {
    $ret = $parser->account();
    var_dump($ret);
} catch (Exception $ex) {
    echo "Error: " . $ex->getMessage() . PHP_EOL;
}