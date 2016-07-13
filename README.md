# Udger client for PHP (data ver. 3)
Local parser is very fast and accurate useragent string detection solution. Enables developers to locally install and integrate a highly-scalable product.
We provide the detection of the devices (personal computer, tablet, Smart TV, Game console etc.), operating system and client SW type (browser, e-mail client etc.).
It also provides information about IP addresses (Public proxies, VPN services, Tor exit nodes, Fake crawlers, Web scrapers .. etc.)

- Tested with more the 50.000 unique user agents.
- Up to date data provided by https://udger.com/

### Requirements
 - php >= 5.3.0
 - ext-sqlite3 (http://php.net/manual/en/book.sqlite3.php)

### Features
- Fast
- Released under the GNU (LGPL v.3)

### Install

    composer install

### Usage
You should review the included examples (`parse.php`, `account.php`)

Here's a quick example:

```php
require_once dirname(__DIR__) . '/vendor/autoload.php';
$factory = new Udger\ParserFactory();
$parser = $factory->getParser();
$parser->setDataDir(sys_get_temp_dir() . "/udgercache/");  
$parser->setUA('Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.97 Safari/537.36');
$parser->setIP("2A02:598:7000:116:0:0:0:101");
$ret = $parser->parse();
echo "<pre>";
print_r($ret);
echo "</pre>";


Array
(
    [user_agent] => Array
        (
            [ua_string] => Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.97 Safari/537.36
            [ua_class] => Browser
            [ua_class_code] => browser
            [ua] => Chrome 48.0.2564.97
            [ua_version] => 48.0.2564.97
            [ua_version_major] => 48
            [ua_uptodate_current_version] => 48
            [ua_family] => Chrome
            [ua_family_code] => chrome
            [ua_family_homepage] => http://www.google.com/chrome/
            [ua_family_vendor] => Google Inc.
            [ua_family_vendor_code] => google_inc
            [ua_family_vendor_homepage] => https://www.google.com/about/company/
            [ua_family_icon] => chrome.png
            [ua_family_icon_big] => chrome_big.png
            [ua_family_info_url] => https://udger.com/resources/ua-list/browser-detail?browser=Chrome
            [ua_engine] => WebKit/Blink
            [os] => Windows 7
            [os_code] => windows_7
            [os_homepage] => https://en.wikipedia.org/wiki/Windows_7
            [os_icon] => windows-7.png
            [os_icon_big] => windows-7_big.png
            [os_info_url] => https://udger.com/resources/ua-list/os-detail?os=Windows 7
            [os_family] => Windows
            [os_family_code] => windows
            [os_family_vendor] => Microsoft Corporation.
            [os_family_vendor_code] => microsoft_corporation
            [os_family_vendor_homepage] => https://www.microsoft.com/about/
            [device_class] => Desktop
            [device_class_code] => desktop
            [device_class_icon] => desktop.png
            [device_class_icon_big] => desktop_big.png
            [device_class_info_url] => https://udger.com/resources/ua-list/device-detail?device=Desktop
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

### Author
- The Udger.com Team (info@udger.com)
                
### old v2 format
If you still use the previous format of the db (v2), please see the branch old_format_v2   
