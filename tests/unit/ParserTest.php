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
        $this->parser = new \Udger\Parser(true);
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
        $this->assertArrayHasKey("flag", $this->parser->parse("random agent"), "flag key is missing");
        $this->assertArrayHasKey("errortext", $this->parser->parse("random agent"), "errortext key is missing");
    }

    public function testIsBot()
    {
        $this->assertNotEmpty($this->parser->isBot("random agent", "0.0.0.0"));
    }

    public function testAccount()
    {
        $this->assertNotEmpty($this->parser->account("test key"));
    }
    
    public function testUpdateData()
    {   
        // false because access key is invalid
        $this->assertFalse($this->parser->updateData());
    }
}