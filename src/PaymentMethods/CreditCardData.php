<?php

namespace GlobalPayments\Api\PaymentMethods;

use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Builders\ManagementBuilder;
use GlobalPayments\Api\Entities\Enums\CvnPresenceIndicator;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\PaymentMethods\Interfaces\ICardData;
use GlobalPayments\Api\Entities\Enums\DccProcessor;
use GlobalPayments\Api\Entities\Enums\DccRateType;
use GlobalPayments\Api\Entities\DccRateData;
use GlobalPayments\Api\Entities\ThreeDSecure;

class CreditCardData extends Credit implements ICardData
{
    /**
     * Card number
     *
     * @var string
     */
    public $number;

    /**
     * Card expiration month
     *
     * @var string
     */
    public $expMonth;

    /**
     * Card expiration year
     *
     * @var string|float
     */
    public $expYear;

    /**
     * Card verification number
     *
     * @var string|float
     */
    public $cvn;

    /**
     * CVN presence indicator
     *
     * @var CvnPresenceIndicator
     */
    public $cvnPresenceIndicator;

    /**
     * Card holder name
     *
     * @var string
     */
    public $cardHolderName;

    /**
     * Card present
     *
     * @var bool
     */
    public $cardPresent;

    /**
     * Card reader present
     *
     * @var bool
     */
    public $readerPresent;

    /**
     * Card type regex patterns
     *
     * @var array
     */
    public static $cardTypes = [
        'Visa' => '/^4/',
        'MC' => '/^(5[1-5]|2[2-7])/',
        'Amex' => '/^3[47]/',
        'DinersClub' => '/^3[0689]/',
        'EnRoute' => '/^2(014|149)/',
        'Discover' => '/^6([045]|22)/',
        'Jcb' => '/^35/',
    ];
    
    /**
     * Set the Card on File storage
     *
     * @var bool
     */
    public $cardBrandTransactionId;

    /**
     * Instantiates a new credit card
     *
     * @return
     */
    public function __construct()
    {
        $this->cardPresent = false;
        $this->readerPresent = false;
        $this->cvnPresenceIndicator = CvnPresenceIndicator::NOT_REQUESTED;
    }

    /**
     * @return string
     */
    public function getShortExpiry()
    {
        if ($this->expMonth != null && $this->expYear != null) {
            return sprintf(
                '%s%s',
                str_pad($this->expMonth, 2, '0', STR_PAD_LEFT),
                substr(str_pad($this->expYear, 4, '0', STR_PAD_LEFT), 2, 2)
            );
        }
        return null;
    }

    /**
     * Gets a card's type based on the BIN
     *
     * @return string
     */
    public function getCardType()
    {
        $number = str_replace(
            [' ', '-'],
            '',
            $this->number
        );

        foreach (static::$cardTypes as $type => $regex) {
            if (1 === preg_match($regex, $this->number)) {
                return $type;
            }
        }

        return 'Unknown';
    }

    /**
     * Verify whether the cardholder is enrolled in 3DS
     *
     * @return bool
     */
    public function verifyEnrolled($amount, $currency, $orderId = null)
    {
        $response = (new AuthorizationBuilder(TransactionType::VERIFY_ENROLLED, $this))
            ->withAmount($amount)
            ->withCurrency($currency)
            ->withOrderId($orderId)
            ->execute();

        if (!empty($response->threeDSecure)) {
            $secureEcom = $response->threeDSecure;
            $secureEcom->setAmount($amount);
            $secureEcom->setCurrency($currency);
            $secureEcom->setOrderId($response->orderId);
            $this->threeDSecure = $secureEcom;

            if (in_array($this->threeDSecure->enrolled, array('N', 'U'))) {
                $this->threeDSecure->xid = null;
                if ($this->threeDSecure->enrolled == 'N') {
                    $this->threeDSecure->eci = $this->cardType == 'MC' ? 1 : 6;
                } elseif ($this->threeDSecure->enrolled == 'U') {
                    $this->threeDSecure->eci = $this->cardType == 'MC' ? 0 : 7;
                }
            }

            return $this->threeDSecure->enrolled == 'Y';
        }
        return false;
    }

    /**
     * @return bool
     */
    public function verifySignature($authorizationResponse, $merchantData = null, $amount = null, $currency = null, $orderId = null)
    {
        if (empty($this->threeDSecure)) {
            $this->threeDSecure = new ThreeDSecure();
        }

        if ($merchantData != null) {
            $this->threeDSecure->setMerchantData($merchantData);
        }

        if ($amount != null) {
            $this->threeDSecure->setAmount($amount);
        }

        if ($currency != null) {
            $this->threeDSecure->setCurrency($currency);
        }

        if ($orderId != null) {
            $this->threeDSecure->setOrderId($orderId);
        }

        $txnReference = new TransactionReference();
        $txnReference->orderId = $this->threeDSecure->getOrderId();

        $response = (new ManagementBuilder(TransactionType::VERIFY_SIGNATURE))
            ->withAmount($this->threeDSecure->getAmount())
            ->withCurrency($this->threeDSecure->getCurrency())
            ->withPayerAuthenticationResponse($authorizationResponse)
            ->withPaymentMethod($txnReference)
            ->execute();

        $this->threeDSecure->status = $response->threeDSecure->status;
        $this->threeDSecure->cavv = $response->threeDSecure->cavv;
        $this->threeDSecure->algorithm = $response->threeDSecure->algorithm;
        $this->threeDSecure->xid = $response->threeDSecure->xid;

        if (in_array($this->threeDSecure->status, array('A', 'Y')) && $response->responseCode == '00') {
            $this->threeDSecure->eci = $response->threeDSecure->eci;
            return true;
        } else {
            $this->threeDSecure->eci = $this->cardType == 'MC' ? 0 : 7;
            return false;
        }
    }
}
