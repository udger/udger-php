<?php

namespace tests\Udger;

class ParserAccountTest extends \Codeception\TestCase\Test
{
    /**
     * @var \FunctionalGuy
     */
    protected $guy;

    protected function _before()
    {
        
    }

    protected function _after()
    {
    }
    
    // tests
    public function testAccount()
    {
        $parser = new \Udger\Parser();
        $parser->setAccessKey("nosuchkey");
        
        $result = $parser->account();
        $this->assertEquals(1, $result["flag"]);
        $this->assertEquals("incorrect accesskey", $result["errortext"]);
    }
    
    public function testAccountMissingKey()
    {
        $parser = new \Udger\Parser();
        
        $result = $parser->account();
        $this->assertEquals(2, $result["flag"]);
        $this->assertEquals("access key not set", $result["errortext"]);
    }
}