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
        $parser = new \Udger\Parser(\Codeception\Util\Stub::makeEmpty("Psr\Log\LoggerInterface"));
        $parser->setAccessKey("nosuchkey");
        
        $this->setExpectedException("Exception", "incorrect accesskey");
        $parser->account();
    }
    
    public function testAccountMissingKey()
    {
        $parser = new \Udger\Parser(\Codeception\Util\Stub::makeEmpty("Psr\Log\LoggerInterface"));
        
        $this->setExpectedException("Exception", "access key not set");
        $parser->account();
    }
    
    public function testAccountValidKey()
    {
        $parser = new \Udger\Parser(\Codeception\Util\Stub::makeEmpty("Psr\Log\LoggerInterface"));
        $parser->setAccessKey("94a4d5510a30ef2e367b27761ebc765b");
        
        $result = $parser->account();
        $this->assertArrayHasKey('flag', $result); // TODO: should be deprecated soon
        $this->assertArrayHasKey('LocalParser', $result);
        $this->assertArrayHasKey('CloudParser', $result);
    }
}