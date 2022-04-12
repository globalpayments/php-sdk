<?php
namespace GlobalPayments\Api\Terminals\UPA\Responses;

class UpaReportHandler extends UpaResponseHandler
{
    
    public $deviceResponseCode;
    
    public $reportType;
    
    public $reportRecords;
    
    public function __construct($jsonResponse)
    {
        $this->parseResponse($jsonResponse);
    }

    public function parseResponse($jsonResponse)
    {
        if (!empty($jsonResponse['data']['cmdResult'])) {
            $this->checkResponse($jsonResponse['data']['cmdResult']);
        }
        
        if (!empty($jsonResponse['data']['data']['OpenTabDetails'])) {
            $this->deviceResponseCode = '00';
            $this->reportType = 'OpenTab';
            $this->reportRecords = $jsonResponse['data']['data']['OpenTabDetails'];
        }
    }
}
