<?php

namespace tests\Udger;

class ParserTest extends \Codeception\TestCase\Test {

    /**
     * @var \UnitGuy
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
        #$this->parser->setAccessKey("udger-php-unit");
        $this->parser->setDataFile("/dev/null");
    }

    protected function _after()
    {
        
    }

    // tests
    public function testSetDataFile()
    {
        $this->expectException(\Exception::class);
        $this->assertTrue($this->parser->setDataFile("/this/is/a/missing/path"));
    }

    public function testSetAccessKey()
    {
        $this->assertTrue($this->parser->setAccessKey("123456"));
    }
    
    public function testSetUA()
    {
        $this->assertTrue($this->parser->setUA("agent"));
    }
    
    public function testSetIP()
    {   
        $this->assertTrue($this->parser->setIP("0.0.0.0"));
    }
    
    public function testParse()
    {   
        #$this->setExpectedException("Exception");
        $this->parser->parse();
    }

    public function testAccount()
    {
        $this->expectException(\Exception::class);
        $this->parser->account("test key");
    }
}