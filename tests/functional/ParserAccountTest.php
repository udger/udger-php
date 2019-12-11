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

        $this->expectException(\Exception::class);
        $parser->account();
    }
    
    public function testAccountMissingKey()
    {
        $parser = new \Udger\Parser(
                \Codeception\Util\Stub::makeEmpty("Psr\Log\LoggerInterface"),
                \Codeception\Util\Stub::makeEmpty("Udger\Helper\IP"));
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('access key not set');
        $parser->account();
    }
}