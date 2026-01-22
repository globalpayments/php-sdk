<?php

namespace GlobalPayments\Api\Terminals\PAX\Responses;

use GlobalPayments\Api\Terminals\Abstractions\IDeviceResponseHandler;
use GlobalPayments\Api\Terminals\PAX\SubGroups\AmountResponse;
use GlobalPayments\Api\Terminals\PAX\SubGroups\HostResponse;
use GlobalPayments\Api\Terminals\PAX\SubGroups\AccountResponse;
use GlobalPayments\Api\Terminals\PAX\SubGroups\TraceResponse;
use GlobalPayments\Api\Terminals\PAX\SubGroups\AvsResponse;
use GlobalPayments\Api\Terminals\PAX\SubGroups\CommercialResponse;
use GlobalPayments\Api\Terminals\PAX\SubGroups\EcomSubGroupResponse;
use GlobalPayments\Api\Terminals\PAX\SubGroups\ExtDataSubGroupResponse;
use GlobalPayments\Api\Terminals\PAX\Entities\Enums\PaxExtData;
use GlobalPayments\Api\Terminals\PAX\Entities\Enums\PaxMessageId;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;
use GlobalPayments\Api\Terminals\PAX\SubGroups\CashierResponse;
use GlobalPayments\Api\Terminals\PAX\SubGroups\CheckResponse;
use GlobalPayments\Api\Utils\EnumUtils;
use GlobalPayments\Api\Terminals\PAX\Entities\Enums\TerminalTransactionType;

class PaxTerminalResponse extends PaxBaseResponse implements IDeviceResponseHandler
{
    // Functional
    public ?string $responseCode = null;
    public ?string $responseText = null;
    public ?string $transactionId = null;
    public ?string $terminalRefNumber = null;
    public ?string $token = null;
    public ?string $signatureStatus = null;
    public mixed $signatureData = null;
    public ?string $hostReferenceNumber = null;
    // Transactional
    public ?string $transactionType = null;
    public ?string $maskedCardNumber = null;
    public ?string $entryMethod = null;
    public ?string $authorizationCode = null;
    public ?string $approvalCode = null;
    public float|int|string|null $transactionAmount = null;
    public float|int|string|null $amountDue = null;
    public float|int|string|null $balanceAmount = null;
    public ?string $cardHolderName = null;
    public ?string $cardBIN = null;
    public ?bool $cardPresent = null;
    public ?string $expirationDate = null;
    public float|int|string|null $tipAmount = null;
    public float|int|string|null $cashBackAmount = null;
    public ?string $avsResponseCode = null;
    public ?string $avsResponseText = null;
    public ?string $cvvResponseCode = null;
    public ?string $cvvResponseText = null;
    public ?bool $taxExempt = null;
    public ?string $taxExemptId = null;
    public ?string $ticketNumber = null;
    public ?string $paymentType = null;
    public ?string $transactionNumber = null;

    // Debug properties
    public ?string $debugLevel = null;
    public ?string $debugFileContents = null;
    public ?string $debugFileLength = null;

    // EMV
    /*
     * The preferred name of the EMV application selected on the EMV card
     */
    public ?string $applicationPreferredName = null;

    /*
     * The aplication label from the EMV card
     */
    public ?string $applicationLabel = null;

    /*
     * the AID (Application ID) of the selected application on the EMV card
     */
    public ?string $applicationId = null;

    /*
     * The cryptogram type used during the transaction
     */
    public mixed $applicationCryptogramType = null;

    /*
     * The actual cryptogram value generated for the transaction
     */
    public ?string $applicationCryptogram = null;

    /*
     * The results of the terminals attempt to verify the cards authenticity.
     */
    public ?string $terminalVerificationResults = null;
    
    public ?string $clerkId = null;
    public ?string $shiftId = null;
    public ?string $saleType = null;
    public ?string $routingNumber = null;
    public ?string $accountNumber = null;
    public ?string $checkNumber = null;
    public ?string $checkType = null;
    public ?string $idType = null;
    public ?string $idValue = null;
    public ?string $DOB = null;
    public ?string $phoneNumber = null;
    public ?string $zipCode = null;
    public float|int|string|null $merchantFee = null;
    public ?string $ebtType = null;
    public ?string $purchaseOrder = null;
    public ?string $customerCode = null;
    public ?string $merchantTaxId = null;
    public ?string $cardBrandTransactionId = null;

    public function __construct($rawResponse, $messageId)
    {
        parent::__construct($rawResponse, $messageId);
    }


    public function mapResponse($messageReader)
    {

        $hostResponse = new HostResponse($messageReader);
        $this->mapTransactionType($messageReader->readToCode(ControlCodes::FS));
        $amountResponse = new AmountResponse($messageReader);
        $accountResponse = new AccountResponse($messageReader);
        $traceResponse = new TraceResponse($messageReader);

        if ($this->messageId === PaxMessageId::T01_RSP_DO_CREDIT) {
            $avsResponse = new AvsResponse($messageReader);
            $commercialResponse = new CommercialResponse($messageReader);
            $ecomResponse = new EcomSubGroupResponse($messageReader);
            $extDataResponse = new ExtDataSubGroupResponse($messageReader);
            
            $this->mapAvsResponse($avsResponse);
            $this->mapCommercialResponse($commercialResponse);
        } else {
            $extDataResponse = new ExtDataSubGroupResponse($messageReader);
        }
        $this->mapHostResponse($hostResponse);
        $this->mapAmountResponse($amountResponse);
        $this->mapAccountResponse($accountResponse);
        $this->mapTraceResponse($traceResponse);
        $this->mapExtDataResponse($extDataResponse);
    }

    public function mapLocalReportResponse($messageReader = null)
    {
        $this->totalReportRecords = $messageReader->readToCode(ControlCodes::FS);
        $this->reportRecordNumber = $messageReader->readToCode(ControlCodes::FS);
        $hostResponse = new HostResponse($messageReader);
        $this->edcType = $messageReader->readToCode(ControlCodes::FS);
        $this->mapTransactionType($messageReader->readToCode(ControlCodes::FS));
        $this->originalTransactionType = $messageReader->readToCode(ControlCodes::FS);
        
        $amountResponse = new AmountResponse($messageReader);
        $accountResponse = new AccountResponse($messageReader);
        $traceResponse = new TraceResponse($messageReader);
        $cashierResponse = new CashierResponse($messageReader);
        $commercialResponse = new CommercialResponse($messageReader);
        $checkResponse = new CheckResponse($messageReader);
        $extDataResponse = new ExtDataSubGroupResponse($messageReader);

        $this->mapHostResponse($hostResponse);
        $this->mapAmountResponse($amountResponse);
        $this->mapAccountResponse($accountResponse);
        $this->mapTraceResponse($traceResponse);
        $this->mapCashierResponse($cashierResponse);
        $this->mapCommercialResponse($commercialResponse);
        $this->mapCheckResponse($checkResponse);
        $this->mapExtDataResponse($extDataResponse);
    }

    private function mapAmountResponse($amountResponse)
    {
        if (!empty($amountResponse)) {
            $this->transactionAmount = $amountResponse->approvedAmount;
            $this->amountDue = $amountResponse->amountDue;
            $this->tipAmount = $amountResponse->tipAmount;
            $this->cashBackAmount = $amountResponse->cashBackAmount;
            $this->balanceAmount = $amountResponse->balance1;
            $this->merchantFee = $amountResponse->merchantFee;
        }
    }

    private function mapHostResponse($hostResponse)
    {
        if (!empty($hostResponse)) {
            $this->responseCode = $hostResponse->hostResponseCode;
            $this->responseText = $hostResponse->hostResponseMessage;
            $this->approvalCode = $hostResponse->authCode;
            $this->hostReferenceNumber = $hostResponse->hostReferenceNumber;
            $this->authorizationCode = $hostResponse->authCode;
            $this->cardBrandTransactionId = $hostResponse->cardBrandTransactionId;
        }
    }

    private function mapExtDataResponse($extDataResponse)
    {
        if (!empty($extDataResponse)) {
            $this->transactionId = $extDataResponse->getExtValue(PaxExtData::HOST_REFERENCE_NUMBER);
            $this->token = $extDataResponse->getExtValue(PaxExtData::TOKEN);
            $this->cardBIN = $extDataResponse->getExtValue(PaxExtData::CARD_BIN);
            $this->signatureStatus = $extDataResponse->getExtValue(PaxExtData::SIGNATURE_STATUS);

            $this->applicationPreferredName = $extDataResponse->getExtValue(PaxExtData::APPLICATION_PREFERRED_NAME);
            $this->applicationLabel = $extDataResponse->getExtValue(PaxExtData::APPLICATION_LABEL);
            $this->applicationId = $extDataResponse->getExtValue(PaxExtData::APPLICATION_ID);
            $this->applicationCryptogramType = 'TC';
            $this->applicationCryptogram = $extDataResponse->getExtValue(PaxExtData::TRANSACTION_CERTIFICATE);
            $this->cardHolderVerificationMethod = $extDataResponse->getExtValue(PaxExtData::CUSTOMER_VERIFICATION_METHOD);
            $this->terminalVerificationResults = $extDataResponse->getExtValue(
                PaxExtData::TERMINAL_VERIFICATION_RESULTS
            );
        }
    }

    private function mapAccountResponse($accountResponse)
    {
        if (!empty($accountResponse)) {
            $this->maskedCardNumber = $accountResponse->accountNumber;
            $this->entryMethod = $accountResponse->entryMode;
            $this->expirationDate = $accountResponse->expireDate;
            $this->paymentType = $accountResponse->cardType;
            $this->cardHolderName = $accountResponse->cardHolder;
            $this->cvvResponseCode = $accountResponse->cvdApprovalCode;
            $this->cvvResponseText = $accountResponse->cvdMessage;
            $this->cardPresent = $accountResponse->cardPresent;
            $this->ebtType = $accountResponse->ebtType;
        }
    }

    private function mapTraceResponse($traceResponse)
    {
        if (!empty($traceResponse)) {
            $this->transactionNumber = $traceResponse->transactionNumber;
            $this->referenceNumber = $traceResponse->referenceNumber;
        }
    }

    private function mapAvsResponse($avsResponse)
    {
        if (!empty($avsResponse)) {
            $this->avsResponseCode = $avsResponse->avsResponseCode;
            $this->avsResponseText = $avsResponse->avsResponseMessage;
        }
    }

    private function mapCommercialResponse($commercialResponse)
    {
        if (!empty($commercialResponse)) {
            $this->purchaseOrder = $commercialResponse->poNumber;
            $this->customerCode = $commercialResponse->customerCode;
            $this->taxExempt = $commercialResponse->taxExempt;
            $this->taxExemptId = $commercialResponse->taxExemptId;
        }
    }
    
    private function mapCashierResponse($cashierResponse)
    {
        if (!empty($cashierResponse)) {
            $this->clerkId = $cashierResponse->clerkId;
            $this->shiftId = $cashierResponse->shiftId;
        }
    }
    
    private function mapCheckResponse($checkResponse)
    {
        if (!empty($checkResponse)) {
            $this->saleType = $checkResponse->saleType;
            $this->routingNumber = $checkResponse->routingNumber;
            $this->accountNumber = $checkResponse->accountNumber;
            $this->checkNumber = $checkResponse->checkNumber;
            $this->checkType = $checkResponse->checkType;
            $this->idType = $checkResponse->idType;
            $this->idValue = $checkResponse->idValue;
            $this->DOB = $checkResponse->DOB;
            $this->phoneNumber = $checkResponse->phoneNumber;
            $this->zipCode = $checkResponse->zipCode;
        }
    }
    
    private function mapTransactionType($transactionType)
    {
        $transactionTypeValue = EnumUtils::parse(new TerminalTransactionType(), $transactionType);
        $this->transactionType = str_replace('_', ' ', $transactionTypeValue);
    }
}
