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
        $this->parser = new \Udger\Parser(
                \Codeception\Util\Stub::makeEmpty("Psr\Log\LoggerInterface"),
                \Codeception\Util\Stub::makeEmpty("Udger\Helper\IP"));
        $this->parser->setDataFile(dirname(__DIR__) . "/fixtures/udgercache/udgerdb_v3.dat");
    }

    protected function _after()
    {
        
    }

    //tests
    public function testParseMultpileAgentStrings()
    {
        $handle = fopen(dirname(__DIR__) . "/fixtures/agents.txt", "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $result = $this->parser->parse($line);
            }
            fclose($handle);
        } else {
            // error opening the file.
        }
    }
}
