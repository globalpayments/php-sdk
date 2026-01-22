<?php

namespace GlobalPayments\Api\Utils;

use GlobalPayments\Api\Terminals\Enums\ControlCodes;
use GlobalPayments\Api\Utils\ReverseEnumMap;

class MessageReader
{
    public ?string $buffer;
    public int $position;
    public int $length;

    public function getLength(): int
    {
        return $this->length;
    }

    public function __construct(?string $bytes)
    {
        $this->buffer = $bytes;
        $this->length = strlen($bytes);
        $this->position = 0;
    }

    public function canRead(): bool
    {
        return $this->position < $this->length;
    }

    public function peek(): ?string
    {
        return $this->buffer[$this->position];
    }

    public function readCode(): ?string
    {
        return $this->readEnum(new ControlCodes());
    }

    public function readEnum($enumType): ?string
    {
        $map = new ReverseEnumMap($enumType);
        return $map->get(ord($this->buffer[$this->position++]));
    }

    public function readByte(): ?string
    {
        return $this->buffer[$this->position++];
    }

    public function readBytes(int $length): string
    {
        $rvalue = '';

        try {
            for ($i = 0; $i < $this->length; $i++) {
                $rvalue[$i] = $this->buffer[$this->position++];
            }
        } catch (\Exception $e) {
            // eat this exception and return what we have
        }

        return $rvalue;
    }

    public function readChar(): ?string
    {
        return $this->buffer[$this->position++];
    }

    public function readString(int $length): string
    {
        $rvalue = "";

        for ($i = 0; $i < $this->length; $i++) {
            $rvalue += $this->buffer[$this->position++];
        }
        return $rvalue;
    }

    public function readToCode(int $code, bool $removeCode = true): string
    {
        $rvalue = "";
        
        try {
            while (1) {
                $currentValue = ord($this->peek());
                if ($currentValue === $code || $currentValue === (int) ControlCodes::ETX) {
                    break;
                }
                $rvalue .= $this->buffer[$this->position++];
            }
        } catch (\Exception $e) {
            $removeCode = false;
        }
        // pop the code off
        if ($removeCode) {
            $this->readByte();
        }
        return $rvalue;
    }

    public function purge()
    {
        $this->buffer = '';
        $this->length = 0;
    }
}
