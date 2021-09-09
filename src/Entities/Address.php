<?php

namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Entities\Exceptions\ArgumentException;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
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

    private static $_MaxLength = array(
        'PhoneNumber' => 20,
        'ZipCode' => 9
    );

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

     /**
     * @param $number
     *
     * @return mixed
     */
    public static function cleanPhoneNumber($number)
    {
        return preg_replace('/\D+/', '', trim($number));
    }
    /**
     * @param $zip
     *
     * @return mixed
     */
    public static function cleanZipCode($zip)
    {
        return preg_replace('/[^0-9A-Za-z]/', '', trim($zip));
    }

    /**
     * This method clean and return the phone number in correct format or throw an exception
     * 
     * @param string $phoneNumber   
     * @return string
     * @throws ArgumentException     
     */
    public static function checkPhoneNumber($phoneNumber) {
        $phoneNumber = self::cleanPhoneNumber($phoneNumber);

        if (!empty($phoneNumber) && strlen($phoneNumber) > self::$_MaxLength['PhoneNumber']) {
            $errorMessage = 'phone number can not be empty or invalid';
            throw new ApiException($errorMessage);
        }
        return $phoneNumber;
    }

    /**
     * This method cleans and return the Zip code in correct format or throw an exception
     * 
     * @param string $zipCode
     * @return string
     * @throws ArgumentException
     */
    public static function checkZipCode($zipCode) {
        $zipCode = self::cleanZipCode($zipCode);

        if (!empty($zipCode) && strlen($zipCode) > self::$_MaxLength['ZipCode']) {
            $errorMessage = 'zip code can not be empty or invalid';
            throw new ApiException($errorMessage);
        }
        return $zipCode;
    }
}
