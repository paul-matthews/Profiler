<?php
require_once(dirname(__FILE__) . '/Observer.php');

interface Observable
{
    public function register(Observer $observer);
}
