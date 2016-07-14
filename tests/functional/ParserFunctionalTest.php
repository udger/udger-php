<?php

namespace tests\Udger;

class ParserFunctionalTest extends \Codeception\TestCase\Test
{
    /**
     * @var \FunctionalGuy
     */
    protected $guy;

    /**
     *
     * @var Parser
     */
    protected $parser;

    protected function _before()
    {
        $this->parser = new \Udger\Parser(
                \Codeception\Util\Stub::makeEmpty("Psr\Log\LoggerInterface"),
                \Codeception\Util\Stub::makeEmpty("Udger\Helper\IP"));
        $this->parser->setDataDir(dirname(__DIR__) . "/fixtures/udgercache/");
    }

    protected function _after()
    {
    }

    // tests
    public function testParse()
    {
        $useragent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.71 Safari/537.36';
        $this->parser->setUA($useragent);
        $result = $this->parser->parse();
        
        $this->arrayHasKey("user_agent", $result);
        $this->assertEquals("Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.71 Safari/537.36", $result["user_agent"]["ua_string"]);
        $this->assertEquals("Browser", $result["user_agent"]["ua_class"]);
        $this->assertEquals("browser", $result["user_agent"]["ua_class_code"]);
        $this->assertEquals("Chrome 39.0.2171.71", $result["user_agent"]["ua"]);
        $this->assertEquals("39.0.2171.71", $result["user_agent"]["ua_version"]);
        $this->assertEquals("39", $result["user_agent"]["ua_version_major"]);
        $this->assertEquals("50", $result["user_agent"]["ua_uptodate_current_version"]);
        $this->assertEquals("Chrome", $result["user_agent"]["ua_family"]);
        $this->assertEquals("chrome", $result["user_agent"]["ua_family_code"]);
        $this->assertEquals("http://www.google.com/chrome/", $result["user_agent"]["ua_family_homepage"]);
        $this->assertEquals("Google Inc.", $result["user_agent"]["ua_family_vendor"]);
        $this->assertEquals("google_inc", $result["user_agent"]["ua_family_vendor_code"]);
        $this->assertEquals("https://www.google.com/about/company/", $result["user_agent"]["ua_family_vendor_homepage"]);
        $this->assertEquals("chrome.png", $result["user_agent"]["ua_family_icon"]);
        $this->assertEquals("chrome_big.png", $result["user_agent"]["ua_family_icon_big"]);
        $this->assertEquals("https://udger.com/resources/ua-list/browser-detail?browser=Chrome", $result["user_agent"]["ua_family_info_url"]);
        $this->assertEquals("WebKit/Blink", $result["user_agent"]["ua_engine"]);
        $this->assertEquals("OS X", $result["user_agent"]["os"]);
        $this->assertEquals("osx", $result["user_agent"]["os_code"]);
        $this->assertEquals("https://en.wikipedia.org/wiki/Mac_OS_X", $result["user_agent"]["os_homepage"]);
        $this->assertEquals("macosx.png", $result["user_agent"]["os_icon"]);
        $this->assertEquals("macosx_big.png", $result["user_agent"]["os_icon_big"]);
        $this->assertEquals("https://udger.com/resources/ua-list/os-detail?os=OS X", $result["user_agent"]["os_info_url"]);
        $this->assertEquals("OS X", $result["user_agent"]["os_family"]);
        $this->assertEquals("osx", $result["user_agent"]["os_family_code"]);
        $this->assertEquals("Apple Computer, Inc.", $result["user_agent"]["os_family_vendor"]);
        $this->assertEquals("apple_inc", $result["user_agent"]["os_family_vendor_code"]);
        $this->assertEquals("http://www.apple.com/", $result["user_agent"]["os_family_vendor_homepage"]);
        $this->assertEquals("Desktop", $result["user_agent"]["device_class"]);
        $this->assertEquals("desktop", $result["user_agent"]["device_class_code"]);
        $this->assertEquals("desktop.png", $result["user_agent"]["device_class_icon"]);
        $this->assertEquals("desktop_big.png", $result["user_agent"]["device_class_icon_big"]);
        $this->assertEquals("https://udger.com/resources/ua-list/device-detail?device=Desktop", $result["user_agent"]["device_class_info_url"]);
        $this->arrayHasKey("crawler_last_seen", $result["user_agent"]);
        $this->arrayHasKey("crawler_category", $result["user_agent"]);
        $this->arrayHasKey("crawler_category_code", $result["user_agent"]);
        $this->arrayHasKey("crawler_respect_robotstxt", $result["user_agent"]);
    }
    
    public function testParseMiss()
    {
        $useragent = 'Lynx/2.8.6rel.5 libwww-FM/2.14 SSL-MM/1.4.1 OpenSSL/1.0.0a';
        $result = $this->parser->parse($useragent);
        
    }
    
    public function testParseRandomString()
    {
        
        $result = $this->parser->parse("loremlipsum");
       
    }
    
    public function testParseEmpty()
    {
        $this->parser->setUA("");
        
        $result = $this->parser->parse();
        $this->assertArrayHasKey("user_agent", $result);
        $this->assertArrayHasKey("ip_address", $result);
    }
    
    public function testParseNull()
    {
        $this->parser->setUA(null);
        
        $result = $this->parser->parse();
        $this->assertArrayHasKey("user_agent", $result);
        $this->assertArrayHasKey("ip_address", $result);
    }
}