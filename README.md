# Udger client for PHP (data ver. 3)
Local parser is very fast and accurate useragent string detection solution. Enables developers to locally install and integrate a highly-scalable product.
We provide the detection of the devices (personal computer, tablet, Smart TV, Game console etc.), operating system, client SW type (browser, e-mail client etc.)
and devices market name (example: Sony Xperia Tablet S, Nokia Lumia 820 etc.).
It also provides information about IP addresses (Public proxies, VPN services, Tor exit nodes, Fake crawlers, Web scrapers, Datacenter name .. etc.)

- Tested with more the 50.000 unique user agents.
- Up to date data provided by https://udger.com/

### Requirements
 - php >= 5.5.0
 - ext-sqlite3 (http://php.net/manual/en/book.sqlite3.php)
 - datafile v3 (udgerdb_v3.dat) from https://data.udger.com/ 

### Features
- Fast
- LRU cache
- Released under the MIT

### Install 
    composer require udger/udger-php

### Usage
You should review the included examples (`parse.php`, `account.php`)

Here's a quick example:

```php
require_once dirname(__DIR__) . '/vendor/autoload.php';
$factory = new Udger\ParserFactory();
$parser = $factory->getParser();
$parser->setDataFile(sys_get_temp_dir() . "/udgercache/udgerdb_v3.dat");
//$parser->setCacheEnable(false);
//$parser->setCacheSize(4000);     
$parser->setUA('Mozilla/5.0 (Linux; Android 5.1.1; SAMSUNG SM-A510F Build/LMY47X) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/3.5 Chrome/38.0.2125.102 Mobile Safari/537.36');
$parser->setIP("2A02:598:7000:116:0:0:0:101");
$ret = $parser->parse();
echo "<pre>";
print_r($ret);
echo "</pre>";


Array
(
        [user_agent] => Array
        (
            [ua_string] => Mozilla/5.0 (Linux; Android 5.1.1; SAMSUNG SM-A510F Build/LMY47X) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/3.5 Chrome/38.0.2125.102 Mobile Safari/537.36
            [ua_class] => Mobile browser
            [ua_class_code] => mobile_browser
            [ua] => Mobile Samsung Browser 3.5
            [ua_version] => 3.5
            [ua_version_major] => 3
            [ua_uptodate_current_version] => 
            [ua_family] => Mobile Samsung Browser
            [ua_family_code] => mobile_samsung_browser
            [ua_family_homepage] => http://developer.samsung.com/internet
            [ua_family_vendor] => SAMSUNG
            [ua_family_vendor_code] => samsung
            [ua_family_vendor_homepage] => http://www.samsung.com/
            [ua_family_icon] => samsung_browser.png
            [ua_family_icon_big] => samsung_browser_big.png
            [ua_family_info_url] => https://udger.com/resources/ua-list/browser-detail?browser=Mobile Samsung Browser
            [ua_engine] => WebKit/Blink
            [os] => Android 5.1 lollipop
            [os_code] => android_5_1
            [os_homepage] => https://en.wikipedia.org/wiki/Android_Lollipop
            [os_icon] => android.png
            [os_icon_big] => android_big.png
            [os_info_url] => https://udger.com/resources/ua-list/os-detail?os=Android 5.1 lollipop
            [os_family] => Android
            [os_family_code] => android
            [os_family_vendor] => Google, Inc.
            [os_family_vendor_code] => google_inc
            [os_family_vendor_homepage] => https://www.google.com/about/company/
            [device_class] => Smartphone
            [device_class_code] => smartphone
            [device_class_icon] => phone.png
            [device_class_icon_big] => phone_big.png
            [device_class_info_url] => https://udger.com/resources/ua-list/device-detail?device=Smartphone
            [device_marketname] => Galaxy A5 (2016)
            [device_vendor] => Samsung
            [device_vendor_code] => samsung
            [device_vendor_homepage] => http://www.samsung.com/
            [device_vendor_icon] => samsung.png
            [crawler_last_seen] => 
            [crawler_category] => 
            [crawler_category_code] => 
            [crawler_respect_robotstxt] => 
        )

    [ip_address] => Array
        (
            [ip] => 2A02:598:7000:116:0:0:0:101
            [ip_ver] => 6
            [ip_classification] => Crawler
            [ip_classification_code] => crawler
            [ip_hostname] => 
            [ip_last_seen] => 2016-02-12 04:28:56
            [ip_country] => Czech Republic
            [ip_country_code] => CZ
            [ip_city] => Prague
            [crawler_name] => SeznamBot/3.2-test1
            [crawler_ver] => 3.2-test1
            [crawler_ver_major] => 3
            [crawler_family] => SeznamBot
            [crawler_family_code] => seznambot
            [crawler_family_homepage] => http://napoveda.seznam.cz/cz/seznambot.html
            [crawler_family_vendor] => Seznam.cz, a.s.
            [crawler_family_vendor_code] => seznam-cz_as
            [crawler_family_vendor_homepage] => http://www.seznam.cz/
            [crawler_family_icon] => seznam.png
            [crawler_family_info_url] => https://udger.com/resources/ua-list/bot-detail?bot=SeznamBot#id12590
            [crawler_last_seen] => 2016-02-15 06:12:28
            [crawler_category] => Search engine bot
            [crawler_category_code] => search_engine_bot
            [crawler_respect_robotstxt] => unknown
            [datacenter_name] => 
            [datacenter_name_code] => 
            [datacenter_homepage] => 
        )

)
```



### Running tests  
    ./vendor/bin/codecept run

### Automatic updates download
- for autoupdate data use Udger data updater (https://udger.com/support/documentation/?doc=62)

### Documentation for programmers
- https://udger.com/pub/documentation/parser/PHP/html/

### Author
- The Udger.com Team (info@udger.com)
                
### old v2 format
If you still use the previous format of the db (v2), please see the branch old_format_v2   
