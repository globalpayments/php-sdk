<?php
namespace GlobalPayments\Api\Entities\OnlineBoarding;

class BoardingResponse
{
    /**
     * @var int
     */
    public $applicationId;
    
    /**
     * @var string
     */
    public $message;
    
    /**
     * @var boolean
     */
    public $hasSignatureLink;
    
    /**
     * @var string
     */
    public $signatureUrl;
}
