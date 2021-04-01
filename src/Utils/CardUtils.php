<?php

namespace GlobalPayments\Api\Utils;

use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Builders\ManagementBuilder;
use GlobalPayments\Api\Entities\Enums\CvnPresenceIndicator;
use GlobalPayments\Api\Entities\Enums\EmvLastChipRead;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\Target;
use GlobalPayments\Api\Entities\Enums\TrackNumber;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\GpApi\DTO\Card;
use GlobalPayments\Api\PaymentMethods\Interfaces\ICardData;
use GlobalPayments\Api\PaymentMethods\Interfaces\IPinProtected;
use GlobalPayments\Api\PaymentMethods\Interfaces\ITrackData;
use Zend\Filter\Compress\Tar;

class CardUtils
{

 
    private static $trackOnePattern = "/%?[B0]?([\d]+)\\^[^\\^]+\\^([\\d]{4})([^?]+)?/";
    private static $trackTwoPattern = "/;([\d]+)=([\d]{4})([^?]+)?/";

    private static $fleetBinMap = [
        'Visa' => [
            '448460' => '448611',
            '448613' => '448615',
            '448617' => '448674',
            '448676' => '448686',
            '448688' => '448699',
            '461400' => '461421',
            '461423' => '461499',
            '480700' => '480899'
        ],
        'MC' => [
            '553231' => '553380',
            '556083' => '556099',
            '556100' => '556599',
            '556700' => '556999',
        ],
        'Wex' => [
            '690046' => '690046',
            '707138' => '707138'
        ],
        'Voyager' => [
            '708885' => '708889'
        ]
    ];
    
    /**
     * Card type regex patterns
     *
     * @var array
     */
    private static $cardTypes = [
        'Visa' => '/^4/',
        'MC' => '/^(5[1-5]|2[2-7])/',
        'Amex' => '/^3[47]/',
        'DinersClub' => '/^3[0689]/',
        'EnRoute' => '/^2(014|149)/',
        'Discover' => '/^6([045]|22)/',
        'Jcb' => '/^35/',
        'Wex' => '/^(?:690046|707138)/',
    ];
    
    public static function parseTrackData($paymentMethod)
    {
        $trackData = $paymentMethod->value;
        preg_match(static::$trackTwoPattern, $trackData, $matches);
        if (!empty($matches[1]) && !empty($matches[2]) && !empty($matches[3])) {
            $pan = $matches[1];
            $expiry = $matches[2];
            $discretionary = $matches[3];

            if (!empty($discretionary)) {
                if (strlen($pan.$expiry.$discretionary) == 37 &&
                        substr(strtolower($discretionary), -1) == 'f') {
                    $discretionary = substr($discretionary, 0, strlen($discretionary) - 1);
                }
            }
            
            $paymentMethod->trackNumber = TrackNumber::TRACK_TWO;
            $paymentMethod->pan = $pan;
            $paymentMethod->expiry = $expiry;
            $paymentMethod->discretionaryData = $discretionary;
            $paymentMethod->trackData = sprintf("%s=%s%s?", $pan, $expiry, $discretionary);
        } else {
            preg_match(static::$trackOnePattern, $trackData, $matches);
            if (!empty($matches[1]) && !empty($matches[2]) && !empty($matches[3])) {
                $paymentMethod->trackNumber = TrackNumber::TRACK_ONE;
                $paymentMethod->pan = $matches[1];
                $paymentMethod->expiry = $matches[2];
                $paymentMethod->discretionaryData = $matches[3];
                $paymentMethod->trackData = str_replace('%', '', $matches[0]);
            }
        }

        return $paymentMethod;
    }
    
    /**
     * Gets a card's type based on the BIN
     *
     * @return string
     */
    public static function getCardType($number)
    {
        $number = str_replace(
            [' ', '-'],
            '',
            $number
        );

        $type = 'Unknown';
        foreach (static::$cardTypes as $type => $regex) {
            if (1 === preg_match($regex, $number)) {
                return $type;
            }
        }
        
        if ($type === "Unknown") {
            if (static::isFleet($type, $number)) {
                $type += "Fleet";
            }
        }

        return $type;
    }
    
    public static function isFleet($cardType, $pan)
    {
        if (!empty($pan)) {
            $compareValue = substr($pan, 0, 6);
            $baseCardType = str_replace("Fleet", '', $cardType);

            if (!empty(static::$fleetBinMap[$baseCardType])) {
                $binRanges = static::$fleetBinMap[$baseCardType];
                foreach ($binRanges as $lowerRange => $upperRange) {
                    if ($compareValue >= $lowerRange && $compareValue <= $upperRange) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Generate a card
     *
     * @param AuthorizationBuilder|ManagementBuilder $builder
     *
     * @return Card
     */
    public static function generateCard($builder)
    {
        $paymentMethod = $builder->paymentMethod;
        $transactionType = $builder->transactionType;
        $funding = ($paymentMethod->paymentMethodType == PaymentMethodType::DEBIT) ? 'DEBIT' : 'CREDIT';
        $card = new Card();
        if ($paymentMethod instanceof ITrackData) {
            $card->track = $paymentMethod->value;
            if ($transactionType == TransactionType::SALE) {
                if (empty($paymentMethod->value)) {
                    $card->number = $paymentMethod->pan;
                    if (!empty($paymentMethod->expiry)) {
                        $card->expiry_month = substr($paymentMethod->expiry, 2, 2);
                        $card->expiry_year = substr($paymentMethod->expiry, 0, 2);
                    }
                }
                if (!empty($builder->emvChipCondition)) {
                    $card->chip_condition = EmvLastChipRead::$emvLastChipRead[$builder->emvChipCondition][Target::GP_API];
                }
                $card->funding = $funding;
            }
        } elseif ($paymentMethod instanceof ICardData) {
            $card->number = $paymentMethod->number;
            $card->expiry_month = str_pad($paymentMethod->expMonth, 2, '0', STR_PAD_LEFT);
            $card->expiry_year = substr(str_pad($paymentMethod->expYear, 4, '0', STR_PAD_LEFT), 2, 2);
            if (!empty($paymentMethod->cvn)) {
                $card->cvv = $paymentMethod->cvn;
                $cvnPresenceIndicator = !empty($paymentMethod->cvn) ? CvnPresenceIndicator::PRESENT :
                    (!empty($paymentMethod->cvnPresenceIndicator) ? $paymentMethod->cvnPresenceIndicator : '');
                $card->cvv_indicator = self::getCvvIndicator($cvnPresenceIndicator);
            }
            $card->funding = $funding;
            //we can't have tag and chip_condition in the same time in the request
            if (!empty($builder->emvLastChipRead) && empty($builder->tagData)) {
                $card->chip_condition = EmvLastChipRead::$emvLastChipRead[$builder->emvLastChipRead][Target::GP_API];
            }
        }

        $billingAddress = !empty($builder->billingAddress) ? $builder->billingAddress->streetAddress1 : '';
        $postalCode = !empty($builder->billingAddress) ? $builder->billingAddress->postalCode : '';
        $card->tag = $builder->tagData;
        $card->avs_address = $billingAddress;
        $card->avs_postal_code = $postalCode;
        $card->authcode = $builder->offlineAuthCode;
        if ($paymentMethod instanceof IPinProtected) {
            $card->pin_block =$paymentMethod->pinBlock;
        }

        return $card;
    }

    public static function getCvvIndicator($cvnPresenceIndicator)
    {
        switch ($cvnPresenceIndicator) {
            case 1:
                $cvvIndicator = 'PRESENT';
                break;
            case 2:
                $cvvIndicator = 'ILLEGIBLE';
                break;
            case 3:
                $cvvIndicator = 'NOT_ON_CARD';
                break;
            default:
                $cvvIndicator = 'NOT_PRESENT';
                break;
        }

        return $cvvIndicator;
    }
}
