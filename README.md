# Udger client for PHP
Local parser is very fast and accurate useragent string detection solution. Enables developers to locally install and integrate a highly-scalable product.
We provide the detection of the devices (personal computer, tablet, Smart TV, Game console etc.), operating system and client SW type (browser, e-mail client etc.).

### Requirements
 - php >= 5.3.0
 - ext-sqlite3 (http://php.net/manual/en/book.sqlite3.php)

### Features
- Fast
- Standalone
- Auto updated datafile and cache from remote server with version checking and checksum datafile
- Released under the GNU (LGPL v.3)

### Install

    composer install

### Usage
You should review the included examples (`parse.php`, `account.php`, `manual_update_data.php` or `full_example.php`)

Here's a quick example:

```php
require_once __DIR__ . '/vendor/autoload.php';
$parser = new Udger\Parser();
$parser->setDataDir(sys_get_temp_dir() . "/udgercache/");
$parser->setAccessKey('XXXXXX');
$parser->setUA('Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.97 Safari/537.36');
$parser->setIP("66.249.64.1");
$ret = $parser->parse();
echo "<pre>";
print_r($ret);
echo "</pre>";
```

### Running tests

    ./vendor/bin/codecept run

### Data for parser
- info: https://udger.com/download/data

### Author
- The Udger.com Team (info@udger.com)
