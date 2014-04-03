<?php

namespace CiscoSystems\PiwikBundle\Connection;

/**
 * Description of ConnectionInterface
 */
interface ConnectionInterface
{
    /**
     * Calls specific method on Piwik API.
     *
     * @param array $params parameters (associative array)
     *
     * @return string response
     */
    public function send( array $params = array());
}