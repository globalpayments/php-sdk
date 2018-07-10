<?php
namespace GlobalPayments\Api\Entities\Reporting;

class AltPaymentData
{
    /**
     * Status
     *
     * @var string
     */
    public $status;
    
    /**
     * Status Message
     *
     * @var string
     */
    public $statusMessage;
    
    /**
     * The Buyer Email Address associated with the AltPayment at the time the transaction was processed.
     *
     * @var string
     */
    public $buyerEmailAddress;
    
    /**
     * Date and time the status was recorded.
     *
     * @var DateTime
     */
    public $stateDate;
    
    /**
     * @var array(AltPaymentProcessorInfo)
     */
    public $processorResponseInfo;
}
