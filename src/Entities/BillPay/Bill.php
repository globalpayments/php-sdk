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
    protected $billType;

    /**
     * The first bill identifier
     * @var ?string
     */
    protected $identifier1;

    /**
     * The second identifier
     * @var ?string
     */
    protected $identifier2;

     /**
     * The third identifier
     * @var ?string
     */
    protected $identifier3;

     /**
     * The fourth identifier
     * @var ?string
     */
    protected $identifier4;

    /**
     * The amount to apply to the bill
     * @var ?string
     */
    protected ?string $amount;

    /**
     * The Customer information for the bill
     * @var Customer
     */
    protected $customer;

    /**
     * The Presentment Status of the bill
     * @var BillPresentment
     */
    protected $billPresentment;

    /**
     * The date the bill is due
     *
     * @internal
     * @var DateTime
     */
    public $dueDate;

    public function getBillType() {
        return $this->billType;
    }

    public function getIdentifier1() {
        return $this->identifier1;
    }

    public function getIdentifier2() {
        return $this->identifier2;
    }

    public function getIdentifier3() {
        return $this->identifier3;
    }

    public function getIdentifier4() {
        return $this->identifier4;
    }

    public function getAmount() {
        return $this->amount;
    }

    public function getCustomer() {
        return $this->customer;
    }

    public function getBillPresentment() 
    {
        return $this->billPresentment;
    }

    public function getDueDate(): DateTime
    {
        return $this->dueDate;
    }

    public function setBillType(string $billType)
    {
        $this->billType = $billType;
    }
    public function setIdentifier1(string $identifier1)
    {
        $this->identifier1 = $identifier1;
    }
    
    public function setIdentifier2(string $identifier2)
    {
        $this->identifier2 = $identifier2;
    }

    public function setIdentifier3(string $identifier3)
    {
        $this->identifier3 = $identifier3;
    }

    public function setIdentifier4(string $identifier4)
    {
        $this->identifier4 = $identifier4;
    }

    public function setAmount(string $amount)
    {
        $this->amount = $amount;
    }

    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
    }

    /**
     * @param BillPresentment $billPresentment
     */
    public function setBillPresentment($billPresentment)
    {
        $this->billPresentment = $billPresentment;
    }

    public function setDueDate(DateTime $dueDate)
    {
        $this->dueDate = $dueDate;
    }

}
