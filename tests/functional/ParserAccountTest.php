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
        $parser = new \Udger\Parser(
                \Codeception\Util\Stub::makeEmpty("Psr\Log\LoggerInterface"),
                \Codeception\Util\Stub::makeEmpty("Udger\Helper\IP"));
        $parser->setAccessKey("nosuchkey");
        
        $this->setExpectedException("Exception");
        $parser->account();
    }
    
    public function testAccountMissingKey()
    {
        $parser = new \Udger\Parser(
                \Codeception\Util\Stub::makeEmpty("Psr\Log\LoggerInterface"),
                \Codeception\Util\Stub::makeEmpty("Udger\Helper\IP"));
        
        $this->setExpectedException("Exception", "access key not set");
        $parser->account();
    }
}