<?php


namespace GlobalPayments\Api\Utils;


use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Enums\IsoCountries;
use GlobalPayments\Api\Entities\Exceptions\ArgumentException;

class CountryUtils
{
    const SIGNIFICANT_COUNTRY_MATCH = 6;
    const SIGNIFICANT_CODE_MATCH = 3;

    /**
     * @param Address $address
     * @param string $countryCode
     * @return bool
     */
    public static function isCountry(Address $address, $countryCode)
    {
        if (!is_null($address->countryCode)) {
            return $address->countryCode === $countryCode;
        } elseif (!is_null($address->country)) {
            $code = self::getCountryCodeByCountry($address->country);
            if (!is_null($code)) {
                return $code === $countryCode;
            }

            return false;
        }

        return false;
    }


    /**
     * Get country name by country code
     * @param string $countryCode
     *
     * @return string|null
     */
    public static function getCountryByCode($countryCode)
    {
        if (empty($countryCode)) {
            return null;
        }
        $countryMapByCode = array_column(IsoCountries::$allCountries, 'alpha2');
        $countryMapByNumericCode = array_column(IsoCountries::$allCountries, 'numeric');

        if (($key = array_search($countryCode, $countryMapByCode, true)) !== false) {
            return IsoCountries::$allCountries[$key]['name'];
        } elseif (($key = array_search($countryCode, $countryMapByNumericCode, true)) !== false) {
            return IsoCountries::$allCountries[$key]['name'];
        } else {
            if (strlen($countryCode) > 3) {
                return null;
            }
            $fuzzyMatchKey = self::fuzzyMatch($countryMapByCode, $countryCode, self::SIGNIFICANT_CODE_MATCH);

            return IsoCountries::$allCountries[$fuzzyMatchKey]['name'];
        }
    }

    /**
     * Get country code by country name
     * @param string $country
     *
     * @return string|null
     */
    public static function getCountryCodeByCountry($country)
    {
        if (empty($country)) {
            return null;
        }
        $countryCodeMapByCountry = array_column(IsoCountries::$allCountries, "name");
        $countryMapByCode = array_column(IsoCountries::$allCountries, 'alpha2');
        $countryMapByNumericCode = array_column(IsoCountries::$allCountries, 'numeric');
        if (($index = array_search($country, $countryCodeMapByCountry, true)) !== false) {
            return IsoCountries::$allCountries[$index]['alpha2'];
        } elseif (($index = array_search($country, $countryCodeMapByCountry)) !== false) {
            return $country;
        } elseif (($index = array_search($country, $countryMapByNumericCode)) !== false) {
            return IsoCountries::$allCountries[$index]['alpha2'];
        } else {
            $fuzzyCountryMatch = self::fuzzyMatch($countryCodeMapByCountry, $country, self::SIGNIFICANT_COUNTRY_MATCH);
            if (!is_null($fuzzyCountryMatch)) {
                return IsoCountries::$allCountries[$fuzzyCountryMatch]['alpha2'];
            } else {
                if (strlen($country) > 3) {
                    return null;
                }

                $fuzzyCodeMatch = self::fuzzyMatch($countryMapByCode, $country, self::SIGNIFICANT_CODE_MATCH);
                if (!is_null($fuzzyCodeMatch)) {
                    return IsoCountries::$allCountries[$fuzzyCodeMatch]['alpha2'];
                }

                return null;
            }
        }
    }

    /**
     * Get all the country details returned as array
     * Exemple: [
     *      [name] => United States of America
     *      [numeric] => 840
     *      [alpha2] => US
     *      [alpha3] => USA
     *   ]
     *
     * @param string $country
     *
     * @return array|null
     */
    public static function getCountryInfo($country)
    {
        if (empty($country)) {
            return null;
        }

        $countryCodeMapByCountry = array_column(IsoCountries::$allCountries, "name");
        $countryMapByCode = array_column(IsoCountries::$allCountries, 'alpha2');
        $countryMapByNumericCode = array_column(IsoCountries::$allCountries, 'numeric');

        if (($index = array_search($country, $countryCodeMapByCountry, true)) !== false) {
            return IsoCountries::$allCountries[$index];
        } else {
            if (($index = array_search($country, $countryCodeMapByCountry)) !== false) {
                return IsoCountries::$allCountries[$index];
            } else {
                if (($index = array_search($country, $countryMapByNumericCode)) !== false) {
                    return IsoCountries::$allCountries[$index];
                } else {
                    $fuzzyCountryMatch = self::fuzzyMatch($countryCodeMapByCountry, $country, self::SIGNIFICANT_COUNTRY_MATCH);
                    if (!is_null($fuzzyCountryMatch)) {
                        return IsoCountries::$allCountries[$fuzzyCountryMatch];
                    } else {
                        if (strlen($country) > 3) {
                            return null;
                        }

                        $fuzzyCodeMatch = self::fuzzyMatch($countryMapByCode, $country, self::SIGNIFICANT_CODE_MATCH);
                        if (!is_null($fuzzyCodeMatch)) {
                            return IsoCountries::$allCountries[$fuzzyCodeMatch];
                        }

                        return null;
                    }
                }
            }
        }
    }

    private static function fuzzyMatch($dict, $query, $significantMatch)
    {
        $rvalue = $rkey = null;
        $matches = [];
        $highScore = -1;
        foreach ($dict as $key => $value) {
            $score = self::fuzzyScore($value, $query);
            if ($score > $significantMatch && $score > $highScore) {
                $matches = [];
                $highScore = $score;
                $rvalue = $value;
                $rkey = $key;
                $matches[$rkey] = $rvalue;
            } elseif ($score == $highScore) {
                $matches[$key] = $value;
            }
        }

        if (count($matches) > 1) {
            return null;
        }

        return $rkey;
    }

    private static function fuzzyScore($term, $query)
    {
        if (empty($term) || empty($query)) {
            throw new ArgumentException("Strings must not be null");
        }

        $termLowerCase = strtolower($term);
        $queryLowerCase = strtolower($query);
        $score = 0;
        $termIndex = 0;
        $previousMatchingCharacterIndex = PHP_INT_MIN;

        for ($queryIndex = 0; $queryIndex < strlen($queryLowerCase); $queryIndex++) {
            $queryChar = $queryLowerCase[$queryIndex];
            $termCharacterMatchFound = false;
            for (; $termIndex < strlen($termLowerCase) && !$termCharacterMatchFound; $termIndex++) {
                $termChar = $termLowerCase[$termIndex];
                if ($queryChar == $termChar) {
                    $score++;
                    if ($previousMatchingCharacterIndex + 1 == $termIndex) {
                        $score += 2;
                    }
                    $previousMatchingCharacterIndex = $termIndex;
                    $termCharacterMatchFound = true;
                }
            }
        }

        return $score;
    }

    public static function getNumericCodeByCountry($country)
    {
        $countryInfo = self::getCountryInfo($country);

        return !empty($countryInfo['numeric']) ? $countryInfo['numeric'] : null;
    }
}