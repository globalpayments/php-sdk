<?php

namespace GlobalPayments\Api\Terminals\UPA\Responses;

use GlobalPayments\Api\Entities\Exceptions\MessageException;
use GlobalPayments\Api\Terminals\Abstractions\IBatchCloseResponse;
use GlobalPayments\Api\Terminals\Entities\{PANDetails, ThreeDesDukpt,TrackData};
use GlobalPayments\Api\Terminals\Enums\DebugLevel;
use GlobalPayments\Api\Terminals\UPA\Entities\Enums\UpaMessageId;

class TransactionResponse extends UpaResponseHandler implements IBatchCloseResponse
{
    public ?string $gatewayResponseMessage;
    public ?string $gatewayResponseCode;
    public ?string $responseId;
    public ?string $responseDateTime;
    public ?string $deviceSerialNum;
    public ?string $appVersion;
    public ?string $osVersion;
    public ?string $emvSdkVersion;
    public ?string $CTLSSdkVersion;
    public string $requestId;
    public ?float $additionalTipAmount;
    public ?float $baseAmount;
    public ?float $taxAmount;
    public ?float $authorizedAmount;
    public ?string $tokenResponseCode;
    public ?string $tokenResponseMessage;
    public ?string $cardBrandTransId;
    public ?string $descriptor;
    public ?int $cavvResultCode;
    public ?int $tokenPANLast;
    public ?int $partialApproval;
    /** @var string|null Batch Identifier */
    public ?string $batchId;
    /**
     * @var int|null Specifies whether or not the card is approved for the Quick Payment Service allowing quick low
     * amount purchases. A QPS transaction bypasses the need for getting a signature or a PIN on
     * credit sale transactions.Possible Values: 0 or 1
     */
    public ?int $qpsQualified;

    /** @var int|null Indicates whether the transaction is Store and Forward or not. Possible Values: 0 or 1 */
    public ?int $storeAndForward;

    /** @var int|null ID of the clerk if in retail mode, and ID of the server if in restaurant mode */
    public ?int $clerkId;

    /** @var string|null Indicates the Invoice number */
    public ?string $invoiceNbr;

    /** @var int|null MasterCard value that may be returned on recurring transactions. The only potential value on an
     * approval is "01"; any of the other values may be returned on a decline
     */
    public ?int $recurringDataCode;

    /** @var int|null Number identifying original transaction */
    public ?int $traceNumber;

    /** @var string|null CPC Indicator is returned for commercial cards if CPC Processing is enabled. */
    public ?string $cpcInd;

    /** @var string|null this will be a json string or null */
    public ?string $paramsConfigured;

    /** @var string|null Indicates the logging type */
    public ?string $debugLevel;

    /** @var string|null Contents of the debug file */
    public ?string $debugFileContents;

    /** @var string|null Length of the debug file contents */
    public ?string $debugFileLength;

    /** @var string|null Scanned Data */
    public ?string $scanData = null;
    /** @var float|null Indicates the base amount due if partial auth, tax, and tip is used in a transaction. */
    public ?float $baseDue;

    /** @var float|null Indicates the tax amount due if partial auth and tax is used in a transaction. */
    public ?float $taxDue;

    /** @var float|null Indicates the tip amount due if partial auth and tip is used in a transaction. */
    public ?float $tipDue;

    /** @var string|null Hashed card value for customer-specific validation */
    public ?string $customHash;

    /** @var int|null Used to inform the POS whether the receipt should print “PIN VERIFIED" */
    public ?int $pinVerified;

    /** @var string|null Specifies the type of EBT transaction whenever an EBT transaction is performed, it has the
     * values of “FoodStamp” and “CashBenefits”.
     */
    public ?string $ebtType;

    /** @var int|null Indicates whether the card entry is fallback or not. Possible Values: 0 or 1 */
    public ?int $fallback;

    /** @var PinDUKPTResponse the Details on the DUKPT PIN entered. */
    public PinDUKPTResponse $pinDUKPT;

    /** @var int|null  Button pressed*/
    public ?int $buttonPressed = null;

    /** @var int|null Index of the menu selected. */
    public ?int $promptMenuSelected = null;
    /** @var string|null Text entered by the user. This is empty if Cancel is tapped. */
    public ?string $valueEntered;

    /** @var float|null Total Extra Charge */
    public ?float $extraChargeTotal;

    public ?string $dataEncryptionType;
    /** @var string|null The card entry method used by the user */
    public ?string $acquisitionType;
    /** @var bool|null This will only be returned if the LUHN check has been performed */
    public ?bool $luhnCheckPassed;

    public ?int $cvv;
    public ?string $emvTags;
    public ?string $emvProcess;
    public PANDetails $PANDetails;

    public TrackData $trackData;

    public ThreeDesDukpt $threeDesDukpt;

    public string $avsResultCode;
    public string $avsResultText;
    public float $totalAmount;
    public string $invoiceNumber;
    public string $merchantId;
    public ?string $batchSeqNbr;
    public string $applicationPAN;
    public string $transactionSequenceCounter;
    public string $additionalTerminalCapabilities;
    public string $unpredictableNumber;
    public string $applicationTransactionCounter;
    public string $terminalType;
    public string $terminalCapabilities;
    public string $terminalCountryCode;
    public string $issuerApplicationData;
    public string $otherAmount;
    public string $amountAuthorized;
    public string $transactionTSI;
    public string $transactionDate;
    public string $transactionCurrencyCode;
    public string $dedicatedDF;
    public string $applicationAIP;
    public string $applicationIdentifier;
    public float $availableBalance;

    public function __construct($jsonResponse)
    {
        $this->parseResponse($jsonResponse);
    }

    /**
     * @throws MessageException
     */
    public function parseResponse(array $jsonResponse): void
    {
        parent::parseResponse($jsonResponse);
        $firstDataNode = $this->isGpApiResponse($jsonResponse) ? $jsonResponse['response'] : $jsonResponse['data'];
        $secondDataNode = $firstDataNode['data'] ?? null;
        if (empty($secondDataNode)) {
            return;
        }
        $this->multipleMessage = $secondDataNode['multipleMessage'] ?? null;
        switch ($this->command) {
            case UpaMessageId::GET_APP_INFO:
                $this->hydrateGetAppInfoData($secondDataNode);
                return;
            case UpaMessageId::GET_PARAM:
                $this->paramsConfigured = json_encode($secondDataNode);
                return;
            case UpaMessageId::GET_DEBUG_LEVEL:
                $this->hydrateGetDebugLevel($secondDataNode);
                return;
            case UpaMessageId::GET_DEBUG_INFO:
                $this->debugFileContents = $secondDataNode['fileContents'] ?? null;
                $this->debugFileLength = $secondDataNode['length'] ?? null;
                return;
            case UpaMessageId::ENTER_PIN:
                if (!empty($secondDataNode['PinDUKPT'])) {
                    $this->hydratePinDUKPT($secondDataNode['PinDUKPT']);
                }
                return;
            case UpaMessageId::SCAN:
                $this->scanData = $secondDataNode['scanData'] ?? null;
                return;
            case UpaMessageId::PROMPT_WITH_OPTIONS:
                $this->buttonPressed = $jsonResponse['data']['data']['button'] ?? null;
                return;
            case UpaMessageId::PROMPT_MENU:
                $this->buttonPressed = $jsonResponse['data']['data']['button'] ?? null;
                $this->promptMenuSelected = $jsonResponse['data']['data']['menuSelected'] ?? null;
                return;
            case UpaMessageId::GENERAL_ENTRY:
                $this->buttonPressed = $jsonResponse['data']['data']['button'] ?? null;
                $this->valueEntered = $jsonResponse['data']['data']['valueEntered'] ?? null;
                return;
            case UpaMessageId::GET_ENCRYPTION_TYPE:
                $this->dataEncryptionType = $jsonResponse['data']['data']['dataEncryptionType'] ?? null;
                return;
            case UpaMessageId::START_CARD_TRANSACTION:
            case UpaMessageId::PROCESS_CARD_TRANSACTION:
                $this->dataEncryptionType = $secondDataNode['dataEncryptionType'] ?? null;
                $this->acquisitionType = $secondDataNode['acquisitionType'] ?? null;
                if (isset($secondDataNode['LuhnCheckPassed'])) {
                    $this->luhnCheckPassed = ($secondDataNode['LuhnCheckPassed'] === "Y");
                }
                if (!empty($secondDataNode['PAN'])) {
                    $this->hydratePANData($secondDataNode['PAN']);
                }
                if (!empty($secondDataNode['trackData'])) {
                    $this->hydrateTrackData($secondDataNode['trackData']);
                }
                $this->emvTags = $secondDataNode['EmvTags'] ?? null;
                $this->cvv = $secondDataNode['Cvv'] ?? null;
                $this->expirationDate = $secondDataNode['expDate'] ?? null;
                $this->scanData = $secondDataNode['scanData'] ?? null;
                if (!empty($secondDataNode['PinDUKPT'])) {
                    $this->hydratePinDUKPT($secondDataNode['PinDUKPT']);
                }
                if (!empty($secondDataNode['3DesDukpt'])) {
                    $this->hydrateThreeDesDukpt($secondDataNode['3DesDukpt']);
                }
                $this->emvProcess = $secondDataNode['EmvProcess'] ?? null;
                break;
            case UpaMessageId::CONTINUE_EMV_TRANSACTION:
            case UpaMessageId::COMPLETE_EMV_TRANSACTION:
            case UpaMessageId::CONTINUE_CARD_TRANSACTION:
                $this->emvTags = $secondDataNode['EmvTags'] ?? null;
                if (!empty($secondDataNode['PinDUKPT'])) {
                    $this->hydratePinDUKPT($secondDataNode['PinDUKPT']);
                }
                break;
            default:
                break;
        }
        $this->hydrateHostData($secondDataNode);
        $this->hydratePaymentData($secondDataNode);
        $this->hydrateTransactionData($secondDataNode);

        $responseMapping = $this->getResponseMapping();
        foreach ($secondDataNode as $responseData) {
            if (is_array($responseData)) {
                foreach ($responseData as $key => $value) {
                    $propertyName = !empty($responseMapping[$key]) ? $responseMapping[$key] : $key;
                    if (property_exists($this, $propertyName)) {
                        $this->{$propertyName} = $value;
                    }
                }
            }
        }
    }

    /*
     * return Array
     *
     * Format [Response text in Json => Property name in UpaResponse class]
     *
     */
    public function getResponseMapping()
    {
        return array(
            //EMV
            '4F' => 'applicationIdentifier',
            '50' => 'applicationLabel',
            '5F20' => 'EmvCardholderName',
            '5F2A' => 'transactionCurrencyCode',
            '5F34' => 'applicationPAN',
            '82' => 'applicationAIP',
            '84' => 'dedicatedDF',
            '8A' => 'authorizedResponse',
            '95' => 'terminalVerificationResults',
            '99' => 'transactionPIN',
            '9A' => 'transactionDate',
            '9B' => 'transactionTSI',
            '9C' => 'transactionType',
            '9F02' => 'amountAuthorized',
            '9F03' => 'otherAmount',
            '9F06' => 'applicationId',
            '9F08' => 'applicationICC',
            '9F0D' => 'applicationIAC',
            '9F0E' => 'IACDenial',
            '9F0F' => 'IACOnline',
            '9F10' => 'issuerApplicationData',
            '9F12' => 'applicationPreferredName',
            '9F1A' => 'terminalCountryCode',
            '9F1E' => 'IFDSerialNumber',
            '9F26' => 'applicationCryptogram',
            '9F27' => 'applicationCryptogramType',
            '9F33' => 'terminalCapabilities',
            '9F35' => 'terminalType',
            '9F36' => 'applicationTransactionCounter',
            '9F37' => 'unpredictableNumber',
            '9F40' => 'additionalTerminalCapabilities',
            '9F41' => 'transactionSequenceCounter',
            'TacDefault' => 'tacDefault',
            'TacDenial' => 'tacDenial',
            'TacOnline' => 'tacOnline',
            '9F34' => 'cardHolderVerificationMethod',
            'batchId' => 'batchId',
            'availableBalance' => 'availableBalance'
        );
    }

    private function hydrateGetAppInfoData($data)
    {
        $this->deviceSerialNum = $data['deviceSerialNum'] ?? null;
        $this->appVersion = $data['appVersion'] ?? null;
        $this->osVersion = $data['OsVersion'] ?? null;
        $this->emvSdkVersion = $data['EmvSdkVersion'] ?? null;
        $this->CTLSSdkVersion = $data['CTLSSdkVersion'] ?? null;
    }

    private function hydrateGetDebugLevel($data)
    {
        $levelValue = bindec($data['debugLevel']);
        $debugLevel = DebugLevel::getKey($levelValue);
        $reflector = new \ReflectionClass(DebugLevel::class);
        $arr = $reflector->getConstants();
        if (is_null($debugLevel)) {
            $arr = array_filter($arr, function ($v) use ($levelValue) {
                return $v > 0 && $v <= $levelValue;
            });
            arsort($arr);
            $diff = $levelValue;
            array_walk_recursive($arr, function ($v, $k) use (&$diff, &$debugLevel, &$arr) {
                $diff = $diff - $v ;
                $debugLevel .= $k . '|';
                if ($diff >= 0) {
                   $arr = array_filter($arr, function ($v) use ($diff) {
                        return $v > 0 && $v <= $diff;
                    });
                }

            });
            $debugLevel = rtrim($debugLevel, '|');
        }

        $this->debugLevel = $debugLevel;
    }

    private function hydrateTransactionData(array $data)
    {
        if (empty($data['transaction'])) {
            return;
        }
        $data = json_decode(json_encode($data));
        $transaction = $data->transaction;
        $this->transactionAmount = $transaction->totalAmount;
        $this->extraChargeTotal = $transaction->extraChargeTotal ?? null;
    }

    private function hydrateHostData(array $data): void
    {
        if (empty($data['host'])) {
            return;
        }
        $data = json_decode(json_encode($data));
        $host = $data->host;
        $this->responseId = $host->responseId ?? null;
        $this->transactionId = $host->referenceNumber ?? null;
        $this->terminalRefNumber = $host->tranNo ?? null;
        $this->responseDateTime = $host->respDateTime ?? null;
        $this->gatewayResponseCode = $host->gatewayResponseCode ?? null;
        $this->gatewayResponseMessage = $host->gatewayResponseMessage ?? null;
        $this->responseCode = $host->responseCode ?? null;
        $this->responseText = $host->responseText ?? null;
        $this->approvalCode = $host->approvalCode ?? null;
        $this->referenceNumber = $host->referenceNumber ?? null;
        $this->avsResponseCode = $host->AvsResultCode ?? null;
        $this->cvvResponseCode = $host->CvvResultCode ?? null;
        $this->avsResponseText = $host->AvsResultText ?? null;
        $this->cvvResponseText = $host->CvvResultText ?? null;
        $this->additionalTipAmount = $host->additionalTipAmount ?? null;
        $this->baseAmount =  $host->baseAmount ?? null;
        $this->tipAmount = $host->tipAmount ?? null;
        $this->taxAmount = $host->taxAmount ?? null;
        $this->cashBackAmount = $host->cashbackAmount ?? null;
        $this->authorizedAmount = $host->authorizedAmount ?? null;
        $this->transactionAmount = $host->totalAmount ?? null;
        $this->merchantFee = $host->surcharge ?? null;
        $this->tokenResponseCode = $host->tokenRspCode ?? null;
        $this->tokenResponseMessage = $host->tokenRspMsg ?? null;
        $this->token = $host->tokenValue ?? null;
        $this->cardBrandTransId = $host->cardBrandTransId ?? null;
        $this->cpcInd = $host->CpcInd ?? null;
        $this->descriptor = $host->txnDescriptor ?? null;
        $this->cavvResultCode = $host->CavvResultCode ?? null;
        $this->tokenPANLast = $host->tokenPANLast ?? null;
        $this->partialApproval = $host->partialApproval ?? null;
        $this->traceNumber = $host->traceNumber ?? null;
        $this->balanceAmount = $host->balanceDue ?? null;
        $this->recurringDataCode = $host->recurringDataCode ?? null;
        $this->baseDue = $host->baseDue ?? null;
        $this->taxDue = $host->taxDue ?? null;
        $this->tipDue = $host->tipDue ?? null;
        $this->customHash = $host->customHash ?? null;
        $this->batchId = $host->batchId ?? null;
        $this->batchSeqNbr = $host->batchSeqNbr ?? null;
    }

    private function hydratePaymentData(array $data): void
    {
        if (empty($data['payment'])) {
            return;
        }
        $data = json_decode(json_encode($data));
        $payment = $data->payment;
        $this->cardHolderName = $payment->cardHolderName ?? null;
        $this->cardType = $payment->cardType ?? null;
        $this->cardGroup = $payment->cardGroup ?? null;
        $this->maskedCardNumber = $payment->maskedPan ?? null;
        $this->signatureStatus = $payment->signatureLine ?? null;
        $this->qpsQualified = $payment->QpsQualified ?? null;
        $this->storeAndForward = $payment->storeAndForward ?? null;
        $this->clerkId = $payment->clerkId ?? null;
        $this->invoiceNbr = $payment->invoiceNbr ?? null;
        $this->expirationDate = $payment->expiryDate ?? null;
        $this->transactionType = $payment->transactionType ?? null;
        $this->entryMethod = $payment->cardAcquisition ?? null;
        $this->ebtType = $payment->ebtType ?? null;
        $this->pinVerified = $payment->PinVerified ?? null;
        $this->fallback = $payment->fallback ?? null;
    }

    private function hydratePANData($panData): void
    {
        $panData = json_decode(json_encode($panData));
        $PANDetails = new PANDetails();
        $PANDetails->clearPAN = $panData->clearPAN ?? null;
        $PANDetails->maskedPAN = $panData->maskedPan ?? null;
        $PANDetails->encryptedPAN = $panData->encryptedPan ?? null;

        $this->PANDetails = $PANDetails;
    }

    private function hydrateTrackData($response): void
    {
        $response = json_decode(json_encode($response));
        $trackData = new TrackData();
        $trackData->clearTrack1 = $response->clearTrack1 ?? null;
        $trackData->maskedTrack1 = $response->maskedTrack1 ?? null;
        $trackData->clearTrack2 = $response->clearTrack2 ?? null;
        $trackData->maskedTrack2 = $response->maskedTrack2 ?? null;
        $trackData->clearTrack3 = $response->clearTrack3 ?? null;
        $trackData->maskedTrack3 = $response->maskedTrack3 ?? null;
        $this->trackData = $trackData;
    }

    private function hydratePinDUKPT($response): void
    {
        $response = json_decode(json_encode($response));
        $this->pinDUKPT = new PinDUKPTResponse();
        $this->pinDUKPT->pinBlock = $response->PinBlock ?? null;
        $this->pinDUKPT->ksn = $response->Ksn ?? null;
    }

    private function hydrateThreeDesDukpt($response): void
    {
        $response = json_decode(json_encode($response));
        $this->threeDesDukpt = new ThreeDesDukpt();
        $this->threeDesDukpt->encryptedBlob = $response->encryptedBlob ?? null;
        $this->threeDesDukpt->ksn = $response->Ksn ?? null;
    }
}
