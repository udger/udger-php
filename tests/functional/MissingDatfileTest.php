<?php


class MissingDatfileTest extends \Codeception\TestCase\Test
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
        $this->assertEquals(3, $result["flag"]);
        $this->assertEquals("data file not found", $result["errortext"]);
        $this->assertNull(@$result["info"]);
    }
}