<?php

namespace GlobalPayments\Api\Terminals\PAX\SubGroups;

use GlobalPayments\Api\Terminals\Abstractions\IRequestSubGroup;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;

class TraceRequest implements IRequestSubGroup
{

    public $referenceNumber;
    public $invoiceNumber;
    public $authCode;
    public $transactionNumber;
    public $timeStamp;
    public $ecrTransactionId;
    public $clientTransactionId;
    public $ps2000;
    public $originalAuthResponse;
    public $originalTraceNumber;
    public $cardBrandTransactionId;
    
    public function getElementString()
    {
        $requestParams = ['referenceNumber', 'invoiceNumber', 'authCode',
            'transactionNumber', 'timeStamp', 'ecrTransactionId', 'clientTransactionId',
            'ps2000', 'originalAuthResponse', 'originalTraceNumber', 'cardBrandTransactionId'];
        $message = '';
        foreach ($requestParams as $val) {
            if (is_null($this->{$val}) === false) {
                $message .= $this->{$val};
            }
            $message .= chr(ControlCodes::US);
        }
        return rtrim($message, chr(ControlCodes::US));
    }
}
