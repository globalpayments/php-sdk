<?php


namespace GlobalPayments\Api\Entities\GpApi\DTO;


class Card
{
    private $number;
    private $expiry_month;
    private $expiry_year;
    private $cvv;
    private $cvv_indicator;
    private $avs_address;
    private $avs_postal_code;
    private $track;
    private $tag;
    private $funding;
    private $chip_condition;
    private $pin_block;
    private $brand_reference;
    private $authcode;

    public function getNumber()
    {
        return $this->number;
    }

    public function setNumber($value)
    {
        $this->number = $value;
    }

    public function getExpireMonth()
    {
        return $this->expiry_month;
    }

    public function setExpireMonth($value)
    {
        $this->expiry_month = $value;
    }

    public function getExpireYear()
    {
        return $this->expiry_year;
    }

    public function setExpireYear($value)
    {
        $this->expiry_year = $value;
    }

    public function getCvv()
    {
        return $this->cvv;
    }

    public function setCvv($value)
    {
        $this->cvv = $value;
    }

    public function getCvvIndicator()
    {
        return $this->cvv_indicator;
    }

    public function setCvvIndicator($value)
    {
        $this->cvv_indicator = $value;
    }

    public function getAvsAddress()
    {
        return $this->avs_address;
    }

    public function setAvsAddress($value)
    {
        $this->avs_address = $value;
    }

    public function getAvsPostalCode()
    {
        return $this->avs_postal_code;
    }

    public function setAvsPostalCode($value)
    {
        $this->avs_postal_code = $value;
    }

    public function getTrack()
    {
        return $this->track;
    }

    public function setTrack($value)
    {
        $this->track = $value;
    }

    public function getTag()
    {
        return $this->tag;
    }

    public function setTag($value)
    {
        $this->tag = $value;
    }

    public function getFunding()
    {
        return $this->funding;
    }

    public function setFunding($value)
    {
        $this->funding = $value;
    }

    public function getChipCondition()
    {
        return $this->chip_condition;
    }

    public function setChipCondition($value)
    {
        $this->chip_condition = $value;
    }

    public function getPinBlock()
    {
        return $this->pin_block;
    }

    public function setPinBlock($value)
    {
        $this->pin_block = $value;
    }

    public function getBrandReference()
    {
        return $this->brand_reference;
    }

    public function setBrandReference($value)
    {
        $this->brand_reference = $value;
    }

    public function getAuthCode()
    {
        return $this->authcode;
    }

    public function setAuthCode($value)
    {
        $this->authcode = $value;
    }
 }