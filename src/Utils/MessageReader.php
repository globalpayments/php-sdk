<?php

namespace GlobalPayments\Api\Utils;

use GlobalPayments\Api\Terminals\Enums\ControlCodes;
use GlobalPayments\Api\Utils\ReverseEnumMap;

class MessageReader
{
    public $buffer;
    public $position;
    public $length = 0;

    public function getLength()
    {
        return $this->length;
    }

    public function __construct($bytes)
    {
        $this->buffer = $bytes;
        $this->length = strlen($bytes);
        $this->position = 0;
    }

    public function canRead()
    {
        return $this->position < $this->length;
    }

    public function peek()
    {
        return $this->buffer[$this->position];
    }

    public function readCode()
    {
        return $this->readEnum(new ControlCodes());
    }

    public function readEnum($enumType)
    {
        $map = new ReverseEnumMap($enumType);
        return $map->get(ord($this->buffer[$this->position++]));
    }

    public function readByte()
    {
        return $this->buffer[$this->position++];
    }

    public function readBytes($length)
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

    public function readChar()
    {
        return $this->buffer[$this->position++];
    }

    public function readString($length)
    {
        $rvalue = "";

        for ($i = 0; $i < $this->length; $i++) {
            $rvalue += $this->buffer[$this->position++];
        }
        return $rvalue;
    }

    public function readToCode($code, $removeCode = true)
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
