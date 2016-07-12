<?php

namespace tests\Udger;

use Udger\ParserFactory;

class ParserFactoryTest extends \Codeception\TestCase\Test {

    /**
     * @var \UnitGuy
     */
    protected $guy;
    
    /**
     *
     * @var ParserFactory
     */
    protected $factory;

    protected function _before()
    {
        $this->factory = new ParserFactory();
    }
    
    public function testGetParser()
    {
        $this->assertInstanceOf("Udger\Parser", $this->factory->getParser());
    }
}