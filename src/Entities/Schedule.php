<?php

namespace GlobalPayments\Api\Entities;

/**
 * A recurring schedule record.
 */
class Schedule extends RecurringEntity
{
    /**
     * The schedule's amount
     *
     * @var float|string|null
     */
    public $amount;

    /**
     * The date/time the schedule was cancelled.
     *
     * @var DateTime|null
     */
    public $cancellationDate;

    /**
     * The schedule's currency.
     *
     * @var string
     */
    public $currency;

    /**
     * The identifier of the customer associated
     * with the schedule.
     *
     * @var string
     */
    public $customerKey;

    /**
     * The description of the schedule.
     *
     * @var string
     */
    public $description;

    /**
     * The device ID associated with a schedule's
     * transactions.
     *
     * @var integer
     */
    public $deviceId;

    /**
     * Indicates if email notifications should be sent.
     *
     * @var boolean
     */
    public $emailNotification;

    /**
     * Indicates when email notifications should be sent.
     *
     * @var EmailReceipt
     */
    public $emailReceipt = 'Never';

    /**
     * The end date of a schedule, if any.
     *
     * @var DateTime|null
     */
    public $endDate;

    /**
     * The schedule's frequency.
     *
     * @see ScheduleFrequency
     * @var ScheduleFrequency
     */
    public $frequency;

    /**
     * Indicates if the schedule has started processing.
     *
     * @var boolean
     */
    public $hasStarted;

    /**
     * The invoice number associated with the schedule.
     *
     * @var string
     */
    public $invoiceNumber;

    /**
     * The schedule's name.
     *
     * @var string
     */
    public $name;

    /**
     * The date/time when the schedule should process next.
     *
     * @var DateTime|null
     */
    public $nextProcessingDate;

    /**
     * The number of payments made to date on the schedule.
     *
     * @var integer|null
     */
    public $numberOfPaymentsRemaining;

    /**
     * The purchase order (PO) number associated with the schedule.
     *
     * @var string
     */
    public $poNumber;

    /**
     * The identifier of the payment method associated with
     * the schedule.
     *
     * @var string
     */
    public $paymentKey;

    /**
     * Indicates when in the month a recurring schedule should run.
     *
     * @var PaymentSchedule
     */
    public $paymentSchedule;

    /**
     * The number of times a failed schedule payment should be
     * reprocessed.
     *
     * @var integer|null
     */
    public $reprocessingCount;

    /**
     * The start date of a schedule.
     *
     * @var DateTime|null
     */
    public $startDate;

    /**
     * The schedule's status.
     *
     * @var string
     */
    public $status;

    /**
     * The schedule's tax amount.
     *
     * @var float|string|null
     */
    public $taxAmount;

    /**
     * Instantiates a new `Schedule` object.
     *
     * @return
     */
    public function __construct($customerKey = null, $paymentKey = null)
    {
        $this->customerKey = $customerKey;
        $this->paymentKey = $paymentKey;
    }

    /**
     * The total amount for the schedule (`Schedule::$amount` + `Schedule::$taxAmount`).
     *
     * @return float|string|null
     */
    public function getTotalAmount()
    {
        return $this->amount + $this->taxAmount;
    }

    /**
     * Sets the schedule's amount.
     *
     * @param float|string $value The amount
     *
     * @return Schedule
     */
    public function withAmount($value)
    {
        $this->amount = $value;
        return $this;
    }

    /**
     * Sets the schedule's currency.
     *
     * @param string $value The currency
     *
     * @return Schedule
     */
    public function withCurrency($value)
    {
        $this->currency = $value;
        return $this;
    }

    /**
     * Sets the schedule's customer.
     *
     * @param string $value The customer's key
     *
     * @return Schedule
     */
    public function withCustomerKey($value)
    {
        $this->customerKey = $value;
        return $this;
    }

    /**
     * Sets the schedule's description.
     *
     * @param string $value The description
     *
     * @return Schedule
     */
    public function withDescription($value)
    {
        $this->description = $value;
        return $this;
    }

    /**
     * Sets the schedule's device ID.
     *
     * @param integer $value The device ID
     *
     * @return Schedule
     */
    public function withDeviceId($value)
    {
        $this->deviceId = $value;
        return $this;
    }

    /**
     * Sets whether the schedule should send email notifications.
     *
     * @param boolean $value The email notification flag
     *
     * @return Schedule
     */
    public function withEmailNotification($value)
    {
        $this->emailNotification = $value;
        return $this;
    }

    /**
     * Sets when the schedule should email receipts.
     *
     * @param EmailReceipt $value When the schedule should email receipts
     *
     * @return Schedule
     */
    public function withEmailReceipt($value)
    {
        $this->emailReceipt = $value;
        return $this;
    }

    /**
     * Sets the schedule's end date.
     *
     * @param DateTime $value The end date
     *
     * @return Schedule
     */
    public function withEndDate($value)
    {
        $this->endDate = $value;
        return $this;
    }

    /**
     * Sets the schedule's frequency.
     *
     * @param string $value The frequency
     *
     * @return Schedule
     */
    public function withFrequency($value)
    {
        $this->frequency = $value;
        return $this;
    }

    /**
     * Sets the schedule's invoice number.
     *
     * @param string $value The invoice number
     *
     * @return Schedule
     */
    public function withInvoiceNumber($value)
    {
        $this->invoiceNumber = $value;
        return $this;
    }

    /**
     * Sets the schedule's name.
     *
     * @param string $value The name
     *
     * @return Schedule
     */
    public function withName($value)
    {
        $this->name = $value;
        return $this;
    }

    /**
     * Sets the schedule's number of payments.
     *
     * @param integer $value The number of payments
     *
     * @return Schedule
     */
    public function withnumberOfPaymentsRemaining($value)
    {
        $this->numberOfPaymentsRemaining = $value;
        return $this;
    }

    /**
     * Sets the schedule's purchase order (PO) number.
     *
     * @param string $value The purchase order (PO) number
     *
     * @return Schedule
     */
    public function withPoNumber($value)
    {
        $this->poNumber = $value;
        return $this;
    }

    /**
     * Sets the schedule's payment method.
     *
     * @param string $value The payment method's key
     *
     * @return Schedule
     */
    public function withPaymentKey($value)
    {
        $this->paymentKey = $value;
        return $this;
    }

    /**
     * Sets the schedule's recurring schedule.
     *
     * @param PaymentSchedule $value The recurring schedule
     *
     * @return Schedule
     */
    public function withPaymentSchedule($value)
    {
        $this->paymentSchedule = $value;
        return $this;
    }

    /**
     * Sets the schedule's reprocessing count.
     *
     * @param integer $value The reprocessing count
     *
     * @return Schedule
     */
    public function withReprocessingCount($value)
    {
        $this->reprocessingCount = $value;
        return $this;
    }

    /**
     * Sets the schedule's start date.
     *
     * @param DateTime $value The start date
     *
     * @return Schedule
     */
    public function withStartDate($value)
    {
        $this->startDate = $value;
        return $this;
    }

    /**
     * Sets the schedule's status.
     *
     * @param string $value The new status
     *
     * @return Schedule
     */
    public function withStatus($value)
    {
        $this->status = $value;
        return $this;
    }

    /**
     * Sets the schedule's tax amount.
     *
     * @param float|string $value The tax amount
     *
     * @return Schedule
     */
    public function withTaxAmount($value)
    {
        $this->taxAmount = $value;
        return $this;
    }
}
