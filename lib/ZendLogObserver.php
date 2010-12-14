<?php
require_once(dirname(__FILE__) . '/Observer.php');
require_once(dirname(__FILE__) . '/Observable.php');
require_once('Zend/Log.php');

class ZendLogObserver implements Observer
{
    private $logger;

    public function __construct(Zend_Log $logger)
    {
        $this->logger = $logger;
    }

    public function notify(Observable $observed, $message = '', $type = DEBUG)
    {
        $this->logger->log($message, $type);
    }

    public function getLog()
    {
        return $this->logger;
    }
}
