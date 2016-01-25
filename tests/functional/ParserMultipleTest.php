<?php

namespace tests\Udger;

class ParserMultipleTest extends \Codeception\TestCase\Test {

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
        $this->parser = new \Udger\Parser();
        $this->parser->SetDataDir(dirname(__DIR__) . "/fixtures/udgercache/");
    }

    protected function _after()
    {
        
    }

    //tests
    public function testParseMultileAgentStrings()
    {
        $handle = fopen(dirname(__DIR__) . "/fixtures/agents.txt", "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $result = $this->parser->parse($line);
                // no errors are allowed
                $this->assertEquals(1, $result["flag"]);
                $this->assertNull(@$result["errortext"]);
            }
            fclose($handle);
        } else {
            // error opening the file.
        }
    }
}
