<?php
require_once(dirname(__FILE__) . '/Observable.php');

interface Observer
{
    const ALERT = 1;
    const CRIT = 2;
    const DEBUG = 7;
    const EMERG = 0;
    const ERR = 3;
    const INFO = 6;
    const NOTICE = 5;
    const WARN = 4;

    public function notify(Observable $observed, $message = '', $type = DEBUG);
}
