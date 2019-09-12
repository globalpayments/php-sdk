<?php

namespace GlobalPayments\Api\Terminals\HPA\Responses;

use GlobalPayments\Api\Terminals\HPA\Entities\HpaResponse;
use GlobalPayments\Api\Terminals\TerminalUtils;
use GlobalPayments\Api\Terminals\Interfaces\IDeviceResponseHandler;

class HpaEodResponse implements IDeviceResponseHandler
{
    private $deviceResponse;

    public function mapResponse($gatewayMultipleResponse)
    {
        $this->deviceResponse = new HpaResponse();
        $this->deviceResponse->responseData = [
            'getBatchReport' => [
                'batchSummary' => [],
                'batchReport' => [],
                'visaCardSummary' => [],
                'mastercardCardSummary' => [],
                'americanExpressCardSummary' => [],
                'discoverCardSummary' => [],
                'paypalCardSummary' => [],
                'batchDetail' => [],
                'transactionDetails' => []
            ]
        ];

        //incase of muliple message needs to be splitted
        //convert the response as array using </SIP> keyword
        $messageList = explode('</SIP>', $gatewayMultipleResponse);

        if (!empty($messageList)) {
            foreach ($messageList as $message) {
                if (!empty($message)) {
                    //process individual <SIP> response
                    if (strpos($message, '<SIP>') !== false && !strpos($message, '</SIP>')) {
                        $message .= '</SIP>';
                        $this->parseEODResponse($message);
                    }
                }
            }
        }
        
        return $this->deviceResponse;
    }

    private function parseEODResponse($gatewayResponse)
    {
        $responseData = TerminalUtils::xmlParse($gatewayResponse);

        if (!empty($responseData)) {
            $responseType = lcfirst($responseData['Response']);

            if (!empty($responseData['Record'])) {
                //for GetBatchReport
                $this->parseResponseRecord($responseData['Record'], $responseType);
            } elseif ($responseData['Response'] == 'EOD') {
                //process main EOD response
                $this->setValue('versionNumber', $responseData, 'Version');
                $this->setValue('ecrId', $responseData, 'ECRId');
                $this->setValue('sipId', $responseData, 'SIPId');
                $this->setValue('deviceId', $responseData, 'DeviceId');
                $this->setValue('response', $responseData, 'Response');
                $this->setValue('multipleMessage', $responseData, 'MultipleMessage');
                $this->setValue('resultCode', $responseData, 'Result');
                $this->setValue('transactionId', $responseData, 'ResponseId');
                $this->setValue('responseCode', $responseData, 'ResponseCode');
                $this->setValue('resultText', $responseData, 'ResultText');
                $this->setValue('requestId', $responseData, 'RequestId');

                //EOD specific
                $this->setValue('reversal', $responseData, 'Reversal');
                $this->setValue('emvOfflineDecline', $responseData, 'EMVOfflineDecline');
                $this->setValue('transactionCertificate', $responseData, 'TransactionCertificate');
                $this->setValue('attachment', $responseData, 'Attachment');
                $this->setValue('sendSAF', $responseData, 'SendSAF');
                $this->setValue('batchClose', $responseData, 'BatchClose');
                $this->setValue('heartBeat', $responseData, 'HeartBeat');
                $this->setValue('eMVPDL', $responseData, 'EMVPDL');
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
                $data["$key"] = $this->formatValue($key, $field['Value']);
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

                            $data[$key][] = $this->formatValue($key, $field['Value']);
                        } else {
                            $data[$key] = $this->formatValue($key, $field['Value']);
                        }
                    }
                }
            }
            if ($recordType == 'getBatchReport' || $recordType == 'sendSAF') {
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
        $key = lcfirst(ucwords($key));
        $key = str_replace(' ', '', $key);
        return $key;
    }
    
    private function formatValue($key, $value)
    {
        if (!empty($value) && (stripos($key, 'amt') !== false || stripos($key, 'amount') !== false)) {
            return TerminalUtils::reformatAmount($value);
        }
        return $value;
    }

    private function formatTableCategory($gatewayRecord)
    {
        $tableCategory = (!empty($gatewayRecord['TableCategory'])) ?
                lcfirst(ucwords(strtolower($gatewayRecord['TableCategory']))) : 'batchReport';
        $tableCategory = str_replace(' ', '', $tableCategory);
        
        $tableCategory = preg_match("/transaction[0-9]+Detail/", $tableCategory) ?
                'transactionDetails' : $tableCategory;
        
        //convert approvedSaf#1Record into approvedSafRecords
        $tableCategory = preg_match("/\#[0-9]+Record/", $tableCategory) ?
                preg_replace("/\#[0-9]+Record/", '', $tableCategory) . 'Records' : $tableCategory;

        return $tableCategory;
    }
}
