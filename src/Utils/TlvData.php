<?php

namespace GlobalPayments\Api\Utils;

class TlvData
{
    private ?string $tag = null;
    private ?string $length = null;
    private ?string $value = null;
    private ?string $description = null;

    public function __construct(?string $tag, ?string $length, ?string $value, ?string $description = null)
    {
        $this->tag = $tag;
        $this->length = $length;
        $this->value = $value;
        $this->description = $description;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function getLength(): ?string
    {
        return $this->length;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getBinaryValue(): string
    {
        $sb = '';
        $bytes = StringUtils::bytesFromHex($this->value);
        foreach ($bytes as $byte) {
            $sb .= substring(decbin($byte & 0xFF) + 0x100, 1);
        }

        return $sb;
    }

    public function getFullValue(): string
    {
        return sprintf("%s%s%s", $this->tag, $this->length, $this->value);
    }
}