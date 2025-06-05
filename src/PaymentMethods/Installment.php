<?php

namespace GlobalPayments\Api\PaymentMethods;

use GlobalPayments\Api\Entities\IInstallmentEntity;
use GlobalPayments\Api\Services\InstallmentService;

class Installment implements IInstallmentEntity
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var DateTime
     */
    public $timeCreated;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $status;

    /**
     * @var string
     */
    public $channel;

    /**
     * @var string
     */
    public $amount;

    /**
     * @var string
     */
    public string $currency;

    /**
     * @var string
     */
    public $country;

    /**
     * @var string
     */
    public $merchantId;

     /**
     * @var string
     */
    public $merchantName;

     /**
     * @var string
     */
    public $accountId;

    /**
     * @var string
     */
    public $accountName;

    /**
     * @var string
     */
    public $reference;

    /**
     * @var string
     */
    public $program;

    /** @var Terms */
    public $terms;

    /**
     * @var string
     */
    public $result;

    /**
     * @var string
     */
    public $message;

    /**
     * @var string
     */
    public $entryMode;
   
    /** @var Card */
    public $card;
    
    /** @var CreditCardData */
    public $cardDetails;

    public string $authCode;

    /** @var Action */
    public $action;

    /**
     * @param string $configName
     * @return Installment
     */
    public function create(string $configName = 'default') : Installment
    {
        return InstallmentService::create($this, $configName);
    }
}