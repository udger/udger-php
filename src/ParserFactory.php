<?php

namespace Udger;

class ParserFactory
{
    private string $dataFile;

    public function __construct(string $dataFile)
    {
        $this->dataFile = $dataFile;
    }

    public function getParser(): ParserInterface
    {
        $parser = new Parser(new Helper\IP());
        $parser->setDataFile($this->dataFile);
        return $parser;
    }
}
