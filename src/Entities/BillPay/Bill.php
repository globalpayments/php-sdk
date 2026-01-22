<?php

namespace GlobalPayments\Api\Entities\BillPay;

use DateTime;
use GlobalPayments\Api\Entities\{Customer};
use GlobalPayments\Api\Entities\Enums\{BillPresentment};

class Bill
{
    /**
     * The name of the bill type
     * @var ?string
     */
    protected ?string $billType = null;

    /**
     * The first bill identifier
     * @var ?string
     */
    protected ?string $identifier1 = null;

    /**
     * The second identifier
     * @var ?string
     */
    protected ?string $identifier2 = null;

     /**
     * The third identifier
     * @var ?string
     */
    protected ?string $identifier3 = null;

     /**
     * The fourth identifier
     * @var ?string
     */
    protected ?string $identifier4 = null;

    /**
     * The amount to apply to the bill
     * @var ?string
     */
    protected ?string $amount = null;

    /**
     * The Customer information for the bill
     * @var Customer
     */
    protected ?Customer $customer = null;

    /**
     * The Presentment Status of the bill
     * @var BillPresentment
     */
    protected ?string $billPresentment = null;

    /**
     * The date the bill is due
     *
     * @internal
     * @var DateTime
     */
    public ?DateTime $dueDate = null;

    public function getBillType(): ?string {
        return $this->billType;
    }

    public function getIdentifier1(): ?string {
        return $this->identifier1;
    }

    public function getIdentifier2(): ?string {
        return $this->identifier2;
    }

    public function getIdentifier3(): ?string {
        return $this->identifier3;
    }

    public function getIdentifier4(): ?string {
        return $this->identifier4;
    }

    public function getAmount(): ?string {
        return $this->amount;
    }

    public function getCustomer(): ?Customer {
        return $this->customer;
    }

    public function getBillPresentment(): ?string
    {
        return $this->billPresentment;
    }

    public function getDueDate(): ?DateTime
    {
        return $this->dueDate;
    }

    public function setBillType(string $billType): void
    {
        $this->billType = $billType;
    }
    public function setIdentifier1(string $identifier1): void
    {
        $this->identifier1 = $identifier1;
    }
    
    public function setIdentifier2(string $identifier2): void
    {
        $this->identifier2 = $identifier2;
    }

    public function setIdentifier3(string $identifier3): void
    {
        $this->identifier3 = $identifier3;
    }

    public function setIdentifier4(string $identifier4): void
    {
        $this->identifier4 = $identifier4;
    }

    public function setAmount(string $amount): void
    {
        $this->amount = $amount;
    }

    public function setCustomer(Customer $customer): void
    {
        $this->customer = $customer;
    }

    /**
     * @param BillPresentment $billPresentment
     */
    public function setBillPresentment(string $billPresentment): void
    {
        $this->billPresentment = $billPresentment;
    }

    public function setDueDate(DateTime $dueDate): void
    {
        $this->dueDate = $dueDate;
    }

}
