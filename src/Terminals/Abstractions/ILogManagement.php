<?php

namespace GlobalPayments\Api\Terminals\Abstractions;

interface ILogManagement
{
    /*
     * Method for log management. SDK interface call this method for logs
     */

    public function setLog($message, $backTrace = '');
}
