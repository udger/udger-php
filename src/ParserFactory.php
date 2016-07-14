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
     * 
     * @param LoggerInterface $logger
     */
    public function __construct($logger = null)
    {
        if (is_null($logger)) {
            // create a log channel
            $logger = new Logger($this->loggerName);
            $logger->pushHandler(new NullHandler());
        }

        $this->logger = $logger;
    }

    /**
     * 
     * @return \Udger\Parser
     */
    public function getParser()
    {   
        return new Parser($this->logger, new Helper\IP());
    }
}
