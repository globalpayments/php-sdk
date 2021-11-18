<?php
namespace GlobalPayments\Api\Terminals\UPA\Responses;

class UpaBatchReport extends UpaResponseHandler
{
    
    public $deviceResponseCode;
    
    public $merchantName;
    
    public $batchSummary;
    
    public $batchTransactions;
    
    public function __construct($jsonResponse)
    {
        $this->parseResponse($jsonResponse);
    }

    public function parseResponse($jsonResponse)
    {
        if (!empty($jsonResponse['data']['cmdResult'])) {
            $this->checkResponse($jsonResponse['data']['cmdResult']);
        }
        
        if (!empty($jsonResponse['data']['data']['batchRecord'])) {
            $batchRecord = $jsonResponse['data']['data']['batchRecord'];
            
            $this->deviceResponseCode = '00';
            $this->merchantName = $jsonResponse['data']['data']['merchantName'];
            $this->batchTransactions = $batchRecord['batchTransactions'];
            unset($batchRecord['batchTransactions']);
            $this->batchSummary = $batchRecord;
        }
    }
}
