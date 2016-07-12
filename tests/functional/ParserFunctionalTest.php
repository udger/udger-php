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
        $this->parser = new \Udger\Parser();
        $this->parser->setDataDir(dirname(__DIR__) . "/fixtures/udgercache/");
        $this->parser->setParseFragments(true);
    }

    protected function _after()
    {
    }

    // tests
    public function testParse()
    {
        $useragent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.71 Safari/537.36';
        $result = $this->parser->parse($useragent);
        
        // flags
        $this->assertEquals(1, $result["flag"]);
        $this->assertNull(@$result["errortext"]);
        
        // info
        $info = $result["info"];
        $this->assertEquals("Browser", $info["type"]);
        $this->assertEquals("Chrome 39.0.2171.71", $info["ua_name"]);
        $this->assertEquals("39.0.2171.71", $info["ua_ver"]);
        $this->assertEquals("Chrome", $info["ua_family"]);
        $this->assertEquals("http://www.google.com/chrome", $info["ua_url"]);
        $this->assertEquals("Google Inc.", $info["ua_company"]);
        $this->assertEquals("http://www.google.com/", $info["ua_company_url"]);
        $this->assertEquals("chrome.png", $info["ua_icon"]);
        $this->assertEquals("WebKit/Blink", $info["ua_engine"]);
        $this->assertEquals("https://udger.com/resources/ua-list/browser-detail?browser=Chrome", $info["ua_udger_url"]);
        $this->assertEquals("unknown", $info["os_name"]);
        $this->assertEquals("unknown", $info["os_family"]);
        $this->assertEquals("unknown", $info["os_url"]);
        $this->assertEquals("unknown", $info["os_company"]);
        $this->assertEquals("unknown", $info["os_company_url"]);
        $this->assertEquals("unknown.png", $info["os_icon"]);
        $this->assertEquals("Personal computer", $info["device_name"]);
        $this->assertEquals("desktop.png", $info["device_icon"]);
        $this->assertEquals("https://udger.com/resources/ua-list/device-detail?device=Personal%20computer", $info["device_udger_url"]);
        
        // fragments
        $this->assertEquals("", $result["fragments"]);
        
        // up to date
        $uptodate = $result["uptodate"];
        $this->assertEquals(true, $uptodate["controlled"]);
        $this->assertEquals(true, $uptodate["is"]);
        $this->assertEquals(39, $uptodate["ver"]);
        $this->assertEquals("http://www.google.com/chrome", $uptodate["url"]);
    }
    
    public function testParseMiss()
    {
        $useragent = 'Lynx/2.8.6rel.5 libwww-FM/2.14 SSL-MM/1.4.1 OpenSSL/1.0.0a';
        $result = $this->parser->parse($useragent);
        
        // flags
        $this->assertEquals(1, $result["flag"]);
        $this->assertNull(@$result["errortext"]);
        
        // info
        $info = $result["info"];
        $this->assertEquals("unknown", $info["type"]);
        $this->assertEquals("unknown", $info["ua_name"]);
        $this->assertEquals("", $info["ua_ver"]);
        $this->assertEquals("unknown", $info["ua_family"]);
        $this->assertEquals("unknown", $info["ua_url"]);
        $this->assertEquals("unknown", $info["ua_company"]);
        $this->assertEquals("unknown", $info["ua_company_url"]);
        $this->assertEquals("unknown.png", $info["ua_icon"]);
        $this->assertEquals("n/a", $info["ua_engine"]);
        $this->assertEquals("", $info["ua_udger_url"]);
        $this->assertEquals("unknown", $info["os_name"]);
        $this->assertEquals("unknown", $info["os_family"]);
        $this->assertEquals("unknown", $info["os_url"]);
        $this->assertEquals("unknown", $info["os_company"]);
        $this->assertEquals("unknown", $info["os_company_url"]);
        $this->assertEquals("unknown.png", $info["os_icon"]);
        $this->assertEquals("Personal computer", $info["device_name"]);
        $this->assertEquals("desktop.png", $info["device_icon"]);
        $this->assertEquals("https://udger.com/resources/ua-list/device-detail?device=Personal%20computer", $info["device_udger_url"]);
        
        // fragments
        $this->assertEquals("", $result["fragments"]);
        
        // up to date
        $uptodate = $result["uptodate"];
        $this->assertEquals(false, $uptodate["controlled"]);
        $this->assertEquals(false, $uptodate["is"]);
        $this->assertEquals("", $uptodate["ver"]);
        $this->assertEquals("", $uptodate["url"]);
    }
    
    public function testParseRandomString()
    {
        
        $result = $this->parser->parse("loremlipsum");
        // flags
        $this->assertEquals(1, $result["flag"]);
        $this->assertNull(@$result["errortext"]);
    }
    
    public function testParseEmpty()
    {
        $this->setExpectedException("Exception", "missing mandatory parameter");
        $result = $this->parser->parse("");
    }
    
    public function testParseNull()
    {
        $this->setExpectedException("Exception", "missing mandatory parameter");
        $result = $this->parser->parse(null);
    }
}