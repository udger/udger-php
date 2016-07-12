<?php

namespace tests\Udger;

class ParserTest extends \Codeception\TestCase\Test
 {

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
        $this->parser = new \Udger\Parser(TRUE);
        $this->parser->setAccessKey("udger-php-unit");
        $this->parser->setDataDir(sys_get_temp_dir());
    }

    protected function _after()
    {
        
    }

    // tests
    public function testSetDataDir()
    {
        $this->assertTrue($this->parser->setDataDir(sys_get_temp_dir()));
    }

    public function testSetParseFragments()
    {
        $this->assertTrue($this->parser->setParseFragments(false));
        $this->assertTrue($this->parser->setParseFragments(true));
    }

    public function testSetAccessKey()
    {
        $this->assertTrue($this->parser->setAccessKey("123456"));
    }

    public function testParse()
    {
        $result = $this->parser->parse("random agent");
    }

    public function testIsBot()
    {
        $this->assertNotEmpty($this->parser->isBot("random agent", "0.0.0.0"));
    }

    public function testAccount()
    {   
        $this->setExpectedException("Exception", "incorrect accesskey");
        $this->parser->account("test key");
    }
    
    public function testUpdateData()
    {   
        // false because access key is invalid
        $this->assertTrue($this->parser->updateData());
    }
}