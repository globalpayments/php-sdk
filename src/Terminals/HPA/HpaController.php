<?php

namespace GlobalPayments\Api\Terminals\HPA;

use GlobalPayments\Api\Terminals\DeviceController;
use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\HPA\HpaTcpInterface;
use GlobalPayments\Api\Terminals\HPA\HpaInterface;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Terminals\HPA\Entities\Enums\HpaMessageId;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Terminals\TerminalUtils;
use GlobalPayments\Api\Terminals\HPA\Requests\HpaSendFileRequest;

/*
 * Main controller class for Heartland payment application
 *
 */

class HpaController extends DeviceController
{
    public $device;

    public $deviceConfig;

    private $builderData = null;

    /*
     * Create interface based on connection mode TCP / HTTP
     */

    public function __construct(ConnectionConfig $config)
    {
        $this->device = new HpaInterface($this);
        $this->requestIdProvider = $config->requestIdProvider;
        $this->deviceConfig = $config;

        switch ($config->connectionMode) {
            case ConnectionModes::TCP_IP:
                $this->deviceInterface = new HpaTcpInterface($config);
                break;
        }
    }

    public function manageTransaction($builder)
    {
        $this->builderData = $builder;
        $xml = new \DOMDocument();
        $transactionType = $this->manageTransactionType($builder->transactionType);
        // Build Request
        $request = $xml->createElement("SIP");
        $request->appendChild($xml->createElement("Version", '1.0'));
        $request->appendChild($xml->createElement("ECRId", '1004'));
        $request->appendChild($xml->createElement("Request", $transactionType));
        $request->appendChild($xml->createElement("RequestId", "%s"));
        $request->appendChild($xml->createElement("TransactionId", $builder->transactionId));

        $totalAmount = TerminalUtils::formatAmount($builder->amount);
        $gratuity = TerminalUtils::formatAmount($builder->gratuity);
        if ($builder->gratuity !== null) {
            $request->appendChild($xml->createElement("TipAmount", $gratuity));
            $totalAmount += $gratuity;
        } else {
            $request->appendChild($xml->createElement("TipAmount", 0));
        }

        $request->appendChild($xml->createElement("TotalAmount", $totalAmount));
        
        $response = $this->send($xml->saveXML($request));
        return $response;
    }

    public function processTransaction($builder)
    {
        $this->builderData = $builder;
        $xml = new \DOMDocument('1.0', 'utf-8');
        $transactionType = $this->manageTransactionType($builder->transactionType);
        $cardGroup = $this->manageCardGroup($builder->paymentMethodType);
        
        $amount = TerminalUtils::formatAmount($builder->amount);
        $gratuity = TerminalUtils::formatAmount($builder->gratuity);
        $taxAmount = TerminalUtils::formatAmount($builder->taxAmount);
        
        // Build Request
        $request = $xml->createElement("SIP");
        $request->appendChild($xml->createElement("Version", '1.0'));
        $request->appendChild($xml->createElement("ECRId", '1004'));
        $request->appendChild($xml->createElement("Request", $transactionType));
        $request->appendChild($xml->createElement("RequestId", "%s"));
        $request->appendChild($xml->createElement("CardGroup", $cardGroup));
        $request->appendChild($xml->createElement("ConfirmAmount", '0'));
        $request->appendChild($xml->createElement("BaseAmount", $amount));

        if ($builder->gratuity !== null) {
            $request->appendChild($xml->createElement("TipAmount", $gratuity));
        } else {
            $request->appendChild($xml->createElement("TipAmount", 0));
        }
        
        if ($builder->taxAmount !== null) {
            $request->appendChild($xml->createElement("TaxAmount", $taxAmount));
        } else {
            $request->appendChild($xml->createElement("TaxAmount", 0));
        }
        
        if ($builder->paymentMethodType == PaymentMethodType::EBT) {
            $request->appendChild($xml->createElement("EBTAmount", $amount));
        }

        $request->appendChild($xml->createElement("TotalAmount", $amount));
        
        $response = $this->send($xml->saveXML($request));
        return $response;
    }

    /*
     * Send control message to device
     *
     * @param string $message control message to device
     *
     * @return HpaResponse parsed device response
     */

    public function send($message, $requestType = null)
    {
        if (strpos($message, "<RequestId>%s</RequestId>") !== false) {
            $requestId = (!empty($this->builderData->requestId)) ?
                        $this->builderData->requestId :
                        $this->requestIdProvider->getRequestId();
            $message = sprintf($message, $requestId);
        }
        //send messaege to gateway
        $this->deviceInterface->send(trim($message), $requestType);
        
        //check response code
        $acceptedCodes = ["0"];
        $this->checkResponse($this->deviceInterface->deviceResponse, $acceptedCodes);
        return $this->deviceInterface->deviceResponse;
    }

    /*
     * Check the device response code
     *
     * @param HpaResponse $gatewayResponse parsed response from device
     * @param array       $acceptedCodes list of success response codes
     *
     * @return raise GatewayException incase of different unexpected code
     */

    public function checkResponse($gatewayResponse, $acceptedCodes)
    {
        if ($acceptedCodes === null) {
            $acceptedCodes = ["00"];
        }
        
        if (!empty($gatewayResponse->resultText) || !empty($gatewayResponse->gatewayResponseMessage)) {
            $responseCode = (string) $gatewayResponse->resultCode;
            $responseMessage = (string) $gatewayResponse->resultText;
            $responseText = (string) $gatewayResponse->gatewayResponseMessage;

            if (!in_array($responseCode, $acceptedCodes)) {
                throw new GatewayException(
                    sprintf(
                        'Unexpected Gateway Response: %s - %s : %s',
                        $responseCode,
                        $responseMessage,
                        $responseText
                    ),
                    $responseCode,
                    $responseMessage
                );
            }
        } else {
            throw new GatewayException('Invalid Gateway Response');
        }
    }

    /*
     * Return message id based on the transaction type
     *
     * @param $transactionType|TransactionType
     * $return HPA message id or UnsupportedTransactionException incase of unknown transaction type
     */

    private function manageTransactionType($transactionType)
    {
        switch ($transactionType) {
            case TransactionType::SALE:
                return HpaMessageId::CREDIT_SALE;
            case TransactionType::AUTH:
                return HpaMessageId::CREDIT_AUTH;
            case TransactionType::CAPTURE:
                return HpaMessageId::CAPTURE;
            case TransactionType::VERIFY:
                return HpaMessageId::CARD_VERIFY;
            case TransactionType::VOID:
                return HpaMessageId::CREDIT_VOID;
            case TransactionType::REFUND:
                return HpaMessageId::CREDIT_REFUND;
            case TransactionType::BALANCE:
                return HpaMessageId::BALANCE;
            case TransactionType::ADD_VALUE:
                return HpaMessageId::ADD_VALUE;
            default:
                throw new UnsupportedTransactionException(
                    'The selected gateway does not support this transaction type.'
                );
        }
    }
    
    public function manageCardGroup($paymentMethodType)
    {
        $cardGroup = $paymentMethodType;
        if ($paymentMethodType == PaymentMethodType::CREDIT) {
            $cardGroup = 'Credit';
        } elseif ($paymentMethodType == PaymentMethodType::DEBIT) {
            $cardGroup = 'Debit';
        } elseif ($paymentMethodType == PaymentMethodType::EBT) {
            $cardGroup = 'EBT';
        } elseif ($paymentMethodType == PaymentMethodType::GIFT) {
            $cardGroup = 'GIFT';
        }
        return $cardGroup;
    }
    
    public function sendFile($sendFileData)
    {
        $sendFile = new HpaSendFileRequest($this->deviceConfig);
        $sendFile->validate($sendFileData);
        
        $fileInfo = $sendFile->getFileInformation($sendFileData);
        
        $initialMessage = "<SIP>"
                . "<Version>1.0</Version>"
                . "<ECRId>1004</ECRId>"
                . "<Request>SendFile</Request>"
                . "<RequestId>%s</RequestId>"
                . "<FileName>".$sendFileData->imageType."</FileName>"
                . "<FileSize>".$fileInfo['fileSize']."</FileSize>"
                . "<MultipleMessage>1</MultipleMessage>"
                . "</SIP>";

        $initialFileResponse = $this->send($initialMessage, HpaMessageId::SEND_FILE);
        
        if (!empty($initialFileResponse) && $initialFileResponse->resultCode == 0) {
            $splitedImageData = str_split($fileInfo['fileData'], $initialFileResponse->maxDataSize);
            $totalMessages = sizeof($splitedImageData);

            for ($i = 0; $i < $totalMessages; $i++) {
                $isMultiple = ( ($i+1) != $totalMessages) ? 1 : 0;
                $subsequentMessage = "<SIP>"
                        . "<Version>1.0</Version>"
                        . "<ECRId>1004</ECRId>"
                        . "<Request>SendFile</Request>"
                        . "<RequestId>%s</RequestId>"
                        . "<FileData>" . $splitedImageData[$i] . "</FileData>"
                        . "<MultipleMessage>" . $isMultiple . "</MultipleMessage>"
                        . "</SIP>";

                $fileResponse = $this->send($subsequentMessage, HpaMessageId::SEND_FILE);
            }
            return $fileResponse;
        }
    }
    
    public function __destruct()
    {
        $this->device->reset();
        $this->device->closeLane();
    }
}
