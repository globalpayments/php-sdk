<?php

namespace GlobalPayments\Api\Terminals;

use GlobalPayments\Api\Terminals\Abstractions\IDeviceMessage;

class DeviceMessage implements IDeviceMessage
{
    /** @var bool */
    public $keepAlive;

    /** @var bool */
    public $awaitResponse;

    /** @var array<byte> */
    private $buffer;

    /** @var array  */
    private $jsonRequest;

    public function __construct(array $buffer)
    {
        $this->buffer = $buffer;
    }
    public function getSendBuffer(): array
    {
        return $this->buffer;
    }

    public function setJsonRequest($jsonRequest)
    {
        $this->jsonRequest = $jsonRequest;
    }

    public function getJsonRequest()
    {
        return $this->jsonRequest;
    }

    public function toString()
    {
        return implode('',$this->buffer);
    }

    public function getRequestField($key)
    {
        if (!is_array($this->jsonRequest)) {
            return;
        }
        $value = null;
        array_walk_recursive($this->jsonRequest, function ($item, $k) use ($key, &$value) {
            if ($k === $key) {
                $value = $item;
                return;
            }
        });

        return $value;
    }
}