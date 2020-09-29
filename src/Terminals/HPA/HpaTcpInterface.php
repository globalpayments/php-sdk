<?php

namespace GlobalPayments\Api\Terminals\HPA;

use GlobalPayments\Api\Terminals\Interfaces\IDeviceCommInterface;
use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\HPA\Entities\HpaResponse;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Terminals\TerminalUtils;
use GlobalPayments\Api\Terminals\HPA\Responses\HpaEodResponse;
use GlobalPayments\Api\Terminals\HPA\Entities\Enums\HpaMessageId;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Terminals\HPA\Responses\HpaSendSafResponse;
use GlobalPayments\Api\Terminals\HPA\Responses\HpaDiagnosticReportResponse;

/*
 * TCP interface for the device connection and parse response
 */
class HpaTcpInterface implements IDeviceCommInterface
{

    /*
     * TCP fsockopen connection object
     */

    private $tcpConnection = null;
    
    /*
     * Device configuration details ConnectionConfig object
     *
     */
    public $deviceDetails;
    
    /*
     * Device final response HpaResponse object
     *
     */
    public $deviceResponse;
    
    /*
     * Device request type
     *
     */
    private $requestType;

    /*
     * @param ConnectionConfig object $config device configuration details
     */
    public function __construct(ConnectionConfig $config)
    {
        $this->deviceDetails = $config;
    }

    /*
     * Create socket connection with device
     * Throws GatewayException incase of connection error
     */
    public function connect()
    {
        if (is_resource($this->tcpConnection)) {
            return;
        }
        
        $errno = '';
        $errstr = '';

        // open socket
        try {
            $this->tcpConnection = pfsockopen(
                'tcp://' . $this->deviceDetails->ipAddress,
                $this->deviceDetails->port,
                $errno,
                $errstr,
                $this->deviceDetails->timeout
            );
        } catch (\Exception $e) {
            throw new GatewayException(
                sprintf('Device connection error: %s - %s', $errno, $errstr),
                $errno,
                $errstr
            );
        }
    }

    /*
     * Close TCP socket connection with device
     */
    public function disconnect()
    {
        // close socket
        if (is_resource($this->tcpConnection)) {
            fclose($this->tcpConnection);
        }
    }

    /*
     * Send request message to device using socket connection
     * @param string $message XML request string
     */
    public function send($message, $requestType = null)
    {
        $this->connect();
        $this->requestType = $requestType;
        $out = '';
        
        if ($this->tcpConnection !== null) {
            try {
                $length = TerminalUtils::findLength($message);
                
                if (false === ($bytes_written = fwrite($this->tcpConnection, $length.$message))) {
                    throw new GatewayException('Device error: failed to write to socket');
                } else {
                    //set time out for read and write
                    stream_set_timeout($this->tcpConnection, $this->deviceDetails->timeout);
                    ob_implicit_flush(true);
                    $multipleMessage = true;
                    do {
                        // read from socket
                        $part = fgets($this->tcpConnection);
                        $out .= $part;
                        
                        //break the loop when there is no multiple message
                        if ($part == "<MultipleMessage>0</MultipleMessage>\n") {
                            $multipleMessage = false;
                        } elseif ($part == "</SIP>\n" && $multipleMessage === false) {
                            break;
                        }
                    } while ($part !== false && !feof($this->tcpConnection));
                    ob_implicit_flush(false);
                }
                if (!empty($out)) {
                    $this->filterResponseMessage($out);
                }
            } catch (\Exception $e) {
                throw new GatewayException(
                    'Device error: ' . $e->getMessage(),
                    null,
                    $e->getMessage()
                );
            }
        }
        return;
    }

    /*
     * Filter the device response. remove control characters
     * Convert multiple string message as array using </SIP> keyword
     *
     * @param XML|String $gatewayResponse XML response from device
     */
    private function filterResponseMessage($gatewayResponse)
    {
        //remove non printable characters
        $gatewayResponse = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F\;]/', '', trim($gatewayResponse));

        if ($this->requestType == HpaMessageId::EOD) {
            //process eod reponse by HpaEodResponse handler
            $responseHandler = new HpaEodResponse();
            $this->deviceResponse = $responseHandler->mapResponse($gatewayResponse);
        } elseif ($this->requestType == HpaMessageId::SENDSAF) {
            //process eod reponse by HpaSendSafResponse handler
            $responseHandler = new HpaSendSafResponse();
            $this->deviceResponse = $responseHandler->mapResponse($gatewayResponse);
        } elseif ($this->requestType == HpaMessageId::GET_DIAGNOSTIC_REPORT) {
            //process Diagnostic Report Response
            $responseHandler = new HpaDiagnosticReportResponse();
            $this->deviceResponse = $responseHandler->mapResponse($gatewayResponse);
        } elseif ($this->requestType == HpaMessageId::GET_LAST_RESPONSE) {
            //process get last response report
            $this->deviceResponse = new HpaResponse();
            $this->parseResponse($gatewayResponse);
        } elseif ($this->requestType == HpaMessageId::GET_INFO_REPORT) {
            $messageList = explode('</SIP>', $gatewayResponse);
            $this->deviceResponse = new HpaResponse();

            if (!empty($messageList)) {
                foreach ($messageList as $message) {
                    if (!empty($message)) {
                        //process individual <SIP> response
                        if (strpos($message, '<SIP>') !== false && !strpos($message, '</SIP>')) {
                            $message .= '</SIP>';
                            $this->parseResponse($message);
                        }
                    }
                }
            }
        } else {
            $this->deviceResponse = new HpaResponse();
            $this->parseResponse($gatewayResponse);
        }
        return;
    }

    /*
     * Parse device response
     *
     * @param XML|String $gatewayResponse XML response from device
     */

    public function parseResponse($gatewayResponse)
    {
        $responseData = TerminalUtils::xmlParse($gatewayResponse);
        
        if (!empty($responseData)) {
            $this->setBasicResponse($responseData);

            //process the records
            if (!empty($responseData['Record'])) {
                if ($this->deviceResponse->response == 'GetAppInfoReport') {
                    $this->parseResponseRecord($responseData['Record'], 'initializeResponse');
                }
            }
        }
        return;
    }

    /*
     * Parse request specific responses
     *
     * @param XML $gatewayRecord
     * @param string $recordType array key to identify the record type
     */

    private function parseResponseRecord($gatewayRecord, $recordType)
    {
        if (!empty($gatewayRecord['Field'])) {
            if (isset($gatewayRecord['Field']['Key']) && isset($gatewayRecord['Field']['Value'])) {
                $field = $gatewayRecord['Field'];
                $key = $this->convertRecordKey($field['Key']);
                $this->deviceResponse->responseData[$recordType]["$key"] = $field['Value'];
            } else {
                //incase of multi dimensional array
                foreach ($gatewayRecord['Field'] as $field) {
                    if (isset($field['Key']) && isset($field['Value'])) {
                        $key = $this->convertRecordKey($field['Key']);
                        $this->deviceResponse->responseData[$recordType]["$key"] = $field['Value'];
                    }
                }
            }
        }
    }
    
    /*
     * Set transaction based response in $deviceResponse
     *
     * @param array $response
     */
    private function parseTransactionResponse($response)
    {
        $this->setValueInResponse('referenceNumber', $response, 'ReferenceNumber');
        $this->setValueInResponse('cardHolderName', $response, 'CardholderName');
        $this->setValueInResponse('entryMethod', $response, 'CardAcquisition');
        $this->setValueInResponse('approvalCode', $response, 'ApprovalCode');
        $this->setValueInResponse('transactionTime', $response, 'TransactionTime');
        $this->setValueInResponse('maskedCardNumber', $response, 'MaskedPAN');
        $this->setValueInResponse('cardType', $response, 'CardType');
        $this->setValueInResponse('signatureStatus', $response, 'SignatureLine');
        
        if (isset($response['TipAdjustAllowed']) && !empty($response['TipAmount'])) {
            $this->deviceResponse->tipAmount = TerminalUtils::reformatAmount(
                $response['TipAmount']
            );
        }
        
        if (isset($response['AuthorizedAmount'])) {
            $this->deviceResponse->transactionAmount = TerminalUtils::reformatAmount(
                $response['AuthorizedAmount']
            );
        }
        
        //EBT response
        $this->setValueInResponse('ebtType', $response, 'EBTType');
        $this->setValueInResponse('pinVerified', $response, 'PinVerified');
    }
    
    /*
     * Set transaction based response in $deviceResponse
     *
     * @param string $propertyName $deviceResponse object property name
     * @param array $response
     * @param string $responseKey response key received from device
     */
    private function setValueInResponse($propertyName, $response, $responseKey)
    {
        if (isset($response[$responseKey])) {
            $this->deviceResponse->{$propertyName} = $response[$responseKey];
        }
    }
    
    private function setBasicResponse($responseData)
    {
        $this->setValueInResponse('versionNumber', $responseData, 'Version');
        $this->setValueInResponse('ecrId', $responseData, 'ECRId');
        $this->setValueInResponse('sipId', $responseData, 'SIPId');
        $this->setValueInResponse('deviceId', $responseData, 'DeviceId');
        $this->setValueInResponse('response', $responseData, 'Response');
        $this->setValueInResponse('multipleMessage', $responseData, 'MultipleMessage');
        $this->setValueInResponse('resultCode', $responseData, 'Result');
        $this->setValueInResponse('transactionId', $responseData, 'ResponseId');
        $this->setValueInResponse('responseCode', $responseData, 'ResponseCode');
        $this->setValueInResponse('resultText', $responseData, 'ResultText');
        $this->setValueInResponse('requestId', $responseData, 'RequestId');
        $this->setValueInResponse('responseText', $responseData, 'ResponseText');
        $this->setValueInResponse('gatewayResponseMessage', $responseData, 'GatewayRspMsg');
        $this->setValueInResponse('isStoredResponse', $responseData, 'StoredResponse');
        $this->setValueInResponse('partialApproval', $responseData, 'PartialApproval');
        $this->setValueInResponse('avsResponseText', $responseData, 'AVSResultText');
        $this->setValueInResponse('avsResponseCode', $responseData, 'AVS');
        $this->setValueInResponse('cvvResponseCode', $responseData, 'CVV');
        $this->setValueInResponse('cvvResponseText', $responseData, 'CVVResultText');
        $this->setValueInResponse('signatureData', $responseData, 'AttachmentData');
        
        if (isset($responseData['BalanceDueAmount'])) {
            $this->deviceResponse->balanceAmountDue = TerminalUtils::reformatAmount(
                $responseData['BalanceDueAmount']
            );
        }
        
        if (isset($responseData['AvailableBalance'])) {
            $this->deviceResponse->availableBalance = TerminalUtils::reformatAmount(
                $responseData['AvailableBalance']
            );
        }

        //set EMV tags
        $this->setValueInResponse('emvApplicationId', $responseData, 'EMV_AID');
        $this->setValueInResponse('emvApplicationName', $responseData, 'EMV_ApplicationName');
        $this->setValueInResponse('emvTerminalVerificationResults', $responseData, 'EMV_TVR');
        $this->setValueInResponse('emvCardHolderVerificationMethod', $responseData, 'EMV_TSI');
        $this->setValueInResponse('emvCryptogramType', $responseData, 'EMV_CryptogramType');
        $this->setValueInResponse('emvCryptogram', $responseData, 'EMV_Cryptogram');
        
        //send file response
        $this->setValueInResponse('maxDataSize', $responseData, 'MaxDataSize');
        
        //process transaction based response
        $transactionRequests = [
            HpaMessageId::CREDIT_SALE,
            HpaMessageId::CREDIT_REFUND,
            HpaMessageId::CREDIT_VOID,
            HpaMessageId::CARD_VERIFY,
            HpaMessageId::CREDIT_AUTH,
            HpaMessageId::CAPTURE
        ];
        if (in_array($this->deviceResponse->response, $transactionRequests)) {
            $this->parseTransactionResponse($responseData);
        }
        
        if ($this->requestType == HpaMessageId::GET_LAST_RESPONSE &&
                !empty($responseData['LastResponse'])) {
            foreach ($responseData['LastResponse'] as $responseKey => $responseValue) {
                $key = ($responseKey == 'SIPId' || $responseKey == 'ECRId') ?
                        strtolower($responseKey) : lcfirst($responseKey);
                $this->deviceResponse->lastResponse[$key] = $responseValue;
            }
        }
    }
    
    private function convertRecordKey($key)
    {
        //convert "APPLICATION MODE" key as "applicationMode"
        $key = strtolower($key);
        $key = lcfirst(ucwords($key));
        $key = str_replace(' ', '', $key);
        return $key;
    }
}
