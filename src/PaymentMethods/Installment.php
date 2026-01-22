<?php

namespace GlobalPayments\Api\PaymentMethods;

use GlobalPayments\Api\Entities\IInstallmentEntity;
use GlobalPayments\Api\Services\InstallmentService;

class Installment implements IInstallmentEntity
{
    /**
     * @var string
     */
    public ?string $id = null;

    /**
     * @var DateTime
     */
    public mixed $timeCreated = null;

    /**
     * @var string
     */
    public ?string $type = null;

    /**
     * @var string
     */
    public ?string $status = null;

    /**
     * @var string
     */
    public ?string $channel = null;

    /**
     * @var string
     */
    public ?string $amount = null;

    /**
     * @var string
     */
    public string $currency;

    /**
     * @var string
     */
    public ?string $country = null;

    /**
     * @var string
     */
    public ?string $merchantId = null;

     /**
     * @var string
     */
    public ?string $merchantName = null;

     /**
     * @var string
     */
    public ?string $accountId = null;

    /**
     * @var string
     */
    public ?string $accountName = null;

    /**
     * @var string
     */
    public ?string $reference = null;

    /**
     * @var string
     */
    public ?string $program = null;

    /** @var Terms */
    public mixed $terms = null;

    /**
     * @var string
     */
    public ?string $result = null;

    /**
     * @var string
     */
    public ?string $message = null;

    /**
     * @var string
     */
    public mixed $entryMode = null;
   
    /** @var Card */
    public mixed $card = null;
    
    /** @var CreditCardData */
    public mixed $cardDetails = null;

    public string $authCode;

    /** @var Action */
    public mixed $action = null;

    /**
     * @param string $configName
     * @return Installment
     */
    public function create(string $configName = 'default') : Installment
    {
        return InstallmentService::create($this, $configName);
    }
}