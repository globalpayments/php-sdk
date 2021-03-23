<?php

namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Entities\Exceptions\ArgumentException;
use GlobalPayments\Api\Utils\CountryUtils;

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
    protected $country;
    
    /**
     * Consumer's country code.
     *
     * @var string
     */
    protected $countryCode;

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->{$property};
        }
    }

    public function __set($property, $value)
    {
        if (in_array(
                $property,
                [
                    'countryCode',
                    'country',
                ]
        )) {
            $countryInfo = CountryUtils::getCountryInfo($value);
        }

        switch ($property)
        {
            case 'countryCode':
                if ($this->country == null && isset($countryInfo)) {
                    $this->country = !empty($countryInfo['name']) ? $countryInfo['name'] : null;
                }
                break;
            case 'country':
                if ($this->countryCode == null && isset($countryInfo)) {
                    $this->countryCode = !empty($countryInfo['alpha2']) ? $countryInfo['alpha2'] : null;
                }
                break;
            default:
                break;
        }

        if (property_exists($this, $property)) {
            return $this->{$property} = $value;
        }

        throw new ArgumentException(sprintf('Property `%s` does not exist on Address', $property));
    }

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

    public function isCountry($countryCode)
    {
        return CountryUtils::isCountry($this, $countryCode);
    }
}
