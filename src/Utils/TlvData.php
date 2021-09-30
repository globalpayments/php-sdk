<?php

namespace GlobalPayments\Api\Utils;

class TlvData
{
    private $tag;
    private $length;
    private $value;
    private $description;

    public function __construct($tag, $length, $value, $description = null)
    {
        $this->tag = $tag;
        $this->length = $length;
        $this->value = $value;
        $this->description = $description;
    }

    public function getTag()
    {
        return $this->tag;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getLength()
    {
        return $this->length;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getBinaryValue()
    {
        $sb = '';
        $bytes = StringUtils::bytesFromHex($this->value);
        foreach ($bytes as $byte) {
            $sb .= substring(decbin($byte & 0xFF) + 0x100, 1);
        }

        return $sb;
    }

    public function getFullValue()
    {
        return sprintf("%s%s%s", $this->tag, $this->length, $this->value);
    }
}