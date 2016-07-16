<?php

namespace Udger;

use Monolog\Logger;
use Monolog\Handler\NullHandler;
use Psr\Log\LoggerInterface;

/**
 * Description of ParserFactory
 *
 * @author tiborb
 */
class ParserFactory {
    
    /**
     *
     * @var string
     */
    private $loggerName = 'udger';
    
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @var string $dataFile path to the data file
     */
    private $dataFile;
    
    /**
     * 
     * @param string $dataFile path to the data file
     * @param LoggerInterface $logger
     */
    public function __construct($dataFile, $logger = null)
    {
        if (is_null($logger)) {
            // create a log channel
            $logger = new Logger($this->loggerName);
            $logger->pushHandler(new NullHandler());
        }
        $this->dataFile = $dataFile;
        $this->logger = $logger;
    }

    /**
     * 
     * @return \Udger\Parser
     */
    public function getParser()
    {   
        $parser = new Parser($this->logger, new Helper\IP());
        $parser->setDataFile($this->dataFile);
        return $parser;
    }
}
