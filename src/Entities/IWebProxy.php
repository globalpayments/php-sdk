<?php


namespace GlobalPayments\Api\Entities;


interface IWebProxy
{
    public function getProxy($destination);

    /**
     * Indicates that the proxy should be used for the specific host
     * Returns true if the proxy server should not be used
     *
     * @param $host
     * @return bool
     */
    public function isBypassed($host);
}