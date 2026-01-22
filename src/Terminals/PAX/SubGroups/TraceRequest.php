<?php

namespace GlobalPayments\Api\Terminals\PAX\SubGroups;

use GlobalPayments\Api\Terminals\Abstractions\IRequestSubGroup;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;

class TraceRequest implements IRequestSubGroup
{

    public ?string $referenceNumber = null;
    public ?string $invoiceNumber = null;
    public ?string $authCode = null;
    public ?string $transactionNumber = null;
    public ?string $timeStamp = null;
    public ?string $ecrTransactionId = null;
    public ?string $clientTransactionId = null;
    public ?string $ps2000 = null;
    public ?string $originalAuthResponse = null;
    public ?string $originalTraceNumber = null;
    public ?string $cardBrandTransactionId = null;
    
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
