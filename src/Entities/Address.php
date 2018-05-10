<?php

namespace GlobalPayments\Api\Entities;

/**
 * Represents a billing or shipping address for the consumer.
 */
class Address
{
    /**
     *
     *
     * @var string
     */
    public $type;

    /**
     * Consumer's street address 1.
     *
     * @var string
     */
    public $streetAddress1;

    /**
     * Consumer's street address 2.
     *
     * @var string
     */
    public $streetAddress2;

    /**
     * Consumer's street address 3.
     *
     * @var string
     */
    public $streetAddress3;

    /**
     * Consumer's city.
     *
     * @var string
     */
    public $city;

    /**
     * Consumer's province.
     *
     * @var string
     */
    public $province;

    /**
     * Consumer's state.
     *
     * Alias of `Address::$province`.
     *
     * @var string
     */
    public $state;

    /**
     * Consumer's postal/zip code.
     *
     * @var string
     */
    public $postalCode;

    /**
     * Consumer's country.
     *
     * @var string
     */
    public $country;
    
    /**
     * Consumer's country code.
     *
     * @var string
     */
    public $countryCode;

    /**
     * Gets the consumer's province.
     *
     * Helper function to get the first non-null value between
     * `Address::$province` and `Address::$state`.
     *
     * @return string
     */
    public function getProvince()
    {
        return isset($this->province)
            ? $this->province
            : $this->state;
    }
}
