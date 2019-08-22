<?php

namespace GlobalPayments\Api\Terminals\HPA\Responses;

use GlobalPayments\Api\Terminals\HPA\Entities\HpaResponse;
use GlobalPayments\Api\Terminals\TerminalUtils;
use GlobalPayments\Api\Terminals\Interfaces\IDeviceResponseHandler;

class HpaDiagnosticReportResponse implements IDeviceResponseHandler
{
    private $deviceResponse;

    public function mapResponse($gatewayMultipleResponse)
    {
        $this->deviceResponse = new HpaResponse();

        //incase of muliple message needs to be splitted
        //convert the response as array using </SIP> keyword
        $messageList = explode('</SIP>', $gatewayMultipleResponse);

        if (!empty($messageList)) {
            foreach ($messageList as $message) {
                if (!empty($message)) {
                    //process individual <SIP> response
                    if (strpos($message, '<SIP>') !== false && !strpos($message, '</SIP>')) {
                        $message .= '</SIP>';
                        $this->parseReportResponse($message);
                    }
                }
            }
        }
        
        return $this->deviceResponse;
    }

    private function parseReportResponse($gatewayResponse)
    {
        $responseData = TerminalUtils::xmlParse($gatewayResponse);

        if (!empty($responseData)) {
            $responseType = lcfirst($responseData['Response']);

            $this->setValue('versionNumber', $responseData, 'Version');
            $this->setValue('ecrId', $responseData, 'ECRId');
            $this->setValue('sipId', $responseData, 'SIPId');
            $this->setValue('deviceId', $responseData, 'DeviceId');
            $this->setValue('response', $responseData, 'Response');
            $this->setValue('multipleMessage', $responseData, 'MultipleMessage');
            $this->setValue('resultCode', $responseData, 'Result');
            $this->setValue('responseCode', $responseData, 'ResponseCode');
            $this->setValue('resultText', $responseData, 'ResultText');
            $this->setValue('requestId', $responseData, 'RequestId');

            if (!empty($responseData['Record'])) {
                //for GetDiagnosticReport
                $this->parseResponseRecord($responseData['Record'], $responseType);
            }
        }
    }

    private function parseResponseRecord($gatewayRecord, $recordType)
    {
        if (!empty($gatewayRecord['Field'])) {
            $data = [];
            if (isset($gatewayRecord['Field']['Key']) && isset($gatewayRecord['Field']['Value'])) {
                $field = $gatewayRecord['Field'];
                $key = $this->formatKey($field['Key']);
                $data["$key"] = $field['Value'];
            } else {
                //incase of multi dimensional array
                foreach ($gatewayRecord['Field'] as $field) {
                    if (isset($field['Key']) && isset($field['Value'])) {
                        $key = $this->formatKey($field['Key']);

                        //convert the string as array when same key value pair repeated
                        if (isset($data[$key])) {
                            if (is_array($data[$key]) === false) {
                                //convert string to array and assign last string as first element of array
                                $prevValue = $data[$key];
                                $data[$key] = [$prevValue];
                            }

                            $data[$key][] = $field['Value'];
                        } else {
                            $data[$key] = $field['Value'];
                        }
                    }
                }
            }
            if ($recordType == 'getdiagnosticreport') {
                $tableCategory = $this->formatTableCategory($gatewayRecord);

                $this->deviceResponse->responseData[$recordType]
                        [$tableCategory] [] = $data;
            } elseif (!empty($this->deviceResponse->responseData[$recordType])) {
                $this->deviceResponse->responseData[$recordType][] = $data;
            } else {
                $this->deviceResponse->responseData[$recordType] = $data;
            }
        }
    }

    /*
     * Set transaction based response in $deviceResponse
     *
     * @param string $propertyName $deviceResponse object property name
     * @param array $response
     * @param string $responseKey response key received from device
     */

    private function setValue($propertyName, $response, $responseKey)
    {
        if (isset($response[$responseKey])) {
            $this->deviceResponse->{$propertyName} = $response[$responseKey];
        }
    }
    
    private function formatKey($key)
    {
        //convert "APPLICATION MODE" key as "applicationMode"
        $key = ucwords(strtolower($key));
        $key = str_replace(' ', '', $key);
        return $key;
    }
    
    private function formatTableCategory($gatewayRecord)
    {
        $tableCategory = (!empty($gatewayRecord['TableCategory'])) ?
                lcfirst(ucwords(strtolower($gatewayRecord['TableCategory']))) : 'batchReport';
        
        $tableCategory = str_replace(' ', '', $tableCategory);
        
        return $tableCategory;
    }
}
