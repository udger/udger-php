<?php

namespace tests\Udger\Helper;

use Udger\Helper\IP;

/**
 *
 * @author tiborb
 */
class ParserFactoryTest extends \Codeception\TestCase\Test {

    /**
     * @var \UnitGuy
     */
    protected $guy;
    
    /**
     *
     * @var IP
     */
    protected $object;

    protected function _before()
    {
        $this->object = new IP();
    }
    
    public function testInterface()
    {
        $this->assertInstanceOf("Udger\Helper\IPInterface", $this->object);
    }
    
    public function testGetInvalidIpVerison()
    {
        $this->assertFalse($this->object->getIpVersion("banana"));
    }
    
    public function testGetEmptyIpVerison()
    {
        $this->assertFalse($this->object->getIpVersion(""));
    }
    
    public function testGetValidIpVerison()
    {
        $this->assertEquals(4, $this->object->getIpVersion("0.0.0.0"));
        $this->assertEquals(4, $this->object->getIpVersion("127.0.0.1"));
    }
    
    public function testGetValidIp6LoopbackVerison()
    {
        $this->assertEquals(6, $this->object->getIpVersion("::1"));
    }
    
    public function testGetValidIp6Verison()
    {
        $this->assertEquals(6, $this->object->getIpVersion("FE80:CD00:0000:0CDE:1257:0000:211E:729C"));
        $this->assertEquals(6, $this->object->getIpVersion("FE80:CD00:0:CDE:1257:0:211E:729C"));
    }
    
    public function testGetIpLong()
    {
        $this->assertEquals(0, $this->object->getIpLong("0.0.0.0"));
    }
}