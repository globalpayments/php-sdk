<?php


namespace GlobalPayments\Api\Entities;


class CustomWebProxy implements IWebProxy
{
    private $uri;
    private $username;
    private $password;

    public function __construct($uri, $username = null, $password = null)
    {
        $this->uri = $uri;
        $this->username = $username;
        $this->password = $password;
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->{$property};
        }
    }

    public function getProxy($destination)
    {
        return $destination;
    }

    public function isBypassed($host)
    {
        return false;
    }
}