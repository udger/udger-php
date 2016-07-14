<?php

namespace Udger;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Description of ParserFactory
 *
 * @author tiborb
 */
class ParserFactory {

    private $loggerName = 'udger';

    public function getParser()
    {
        // create a log channel
        $log = new Logger($this->loggerName);
        $log->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

        return new Parser($log, new Helper\IP());
    }
}
