<?php

namespace GlobalPayments\Api\Terminals\UPA\Responses;

use GlobalPayments\Api\Terminals\UPA\Entities\UpaResponse;

class UpaDeviceResponse extends UpaResponse
{

    public function __construct($jsonResponse, $messageId)
    {
        $this->parseResponse($jsonResponse);
    }

    public function parseResponse($jsonResponse)
    {
        if (!empty($jsonResponse['data']['cmdResult'])) {
            $this->checkResponse($jsonResponse['data']['cmdResult']);
            
            if ($jsonResponse['data']['cmdResult']['result'] === 'Success') {
                $this->deviceResponseCode = '00';
            }
        }
        
        if (!empty($jsonResponse['data']['data'])) {
            $responseMapping = $this->getResponseMapping();
            foreach ($jsonResponse['data']['data'] as $responseData) {
                if (is_array($responseData)) {
                    foreach ($responseData as $key => $value) {
                        $propertyName = !empty($responseMapping[$key]) ? $responseMapping[$key] : $key;
                        $this->{$propertyName} = $value;
                    }
                }
            }
        }
    }
}
