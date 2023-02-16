<?php

namespace GlobalPayments\Api\Gateways;

use DOMDocument;
use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Builders\ManagementBuilder;
use GlobalPayments\Api\Builders\ReportBuilder;
use GlobalPayments\Api\Builders\TransactionBuilder;
use GlobalPayments\Api\Entities\BatchSummary;
use GlobalPayments\Api\Entities\Enums\CardDataSource;
use GlobalPayments\Api\Entities\Enums\CardType;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\Interfaces\ITrackData;
use GlobalPayments\Api\Entities\Enums\CommercialIndicator;
use GlobalPayments\Api\Entities\Enums\CreditDebitIndicator;
use GlobalPayments\Api\Entities\Enums\OperatingEnvironment;
use GlobalPayments\Api\Entities\Enums\Secure3dVersion;
use GlobalPayments\Api\Entities\Enums\StoredCredentialInitiator;
use GlobalPayments\Api\Entities\Enums\TrackNumber;
use GlobalPayments\Api\Entities\Exceptions\ConfigurationException;
use GlobalPayments\Api\Entities\ThreeDSecure;
use GlobalPayments\Api\Utils\StringUtils;
use GlobalPayments\Api\Utils\AmountUtils;

class TransITConnector extends XmlGateway implements IPaymentGateway
{
    public $merchantId ;
    public $deviceId ;
    public $transactionKey;
    public $manifest;
    public $userId;
    public $password;
    public $developerId;
    public $acceptorConfig;
    
    public $supportsHostedPayments = false;

    public function supportsOpenBanking() : bool
    {
        return false;
    }

    public function processAuthorization(AuthorizationBuilder $builder)
    {
        if (empty($this->transactionKey) && empty($this->manifest)) {
            throw new ConfigurationException('transactionKey/manifest is required for this transaction.');
        }

        $xml = new DOMDocument();
        $paymentMethod = $builder->paymentMethod;
        $commercialDataSubmitted = !empty($builder->commercialData);

        if ($paymentMethod->cardType === CardType::AMEX && !empty($paymentMethod->cvn)) {
            $cardDataInputMode = 'MANUALLY_ENTERED_WITH_KEYED_CID_AMEX_JCB';
        } else {
            if ($this->acceptorConfig->operatingEnvironment === OperatingEnvironment::ON_MERCHANT_PREMISES_ATTENDED) {
                $cardDataInputMode = 'KEY_ENTERED_INPUT';
            } else {
                $cardDataInputMode = 'ELECTRONIC_COMMERCE_NO_SECURITY_CHANNEL_ENCRYPTED_SET_WITHOUT_CARDHOLDER_CERTIFICATE';
            }
        }
        
        $transaction = $xml->createElement($this->mapRequestType($builder));
        $transaction->appendChild($xml->createElement('deviceID', $this->deviceId));
        $transaction->appendChild($xml->createElement('transactionKey', $this->transactionKey));

        if ($paymentMethod instanceof CreditCardData) {
            if (!empty($this->acceptorConfig->cardDataSource)) {
                $transaction->appendChild($xml->createElement('cardDataSource', $this->acceptorConfig->cardDataSource));
            } else {
                if ($paymentMethod->readerPresent) {
                    $transaction->appendChild($xml->createElement('cardDataSource', $paymentMethod->cardPresent ? "MANUAL" : "PHONE"));
                } else {
                    $transaction->appendChild($xml->createElement('cardDataSource', $paymentMethod->cardPresent ? "MANUAL" : "INTERNET"));
                }
            }
        } elseif ($paymentMethod instanceof ITrackData) {
            $transaction->appendChild($xml->createElement('cardDataSource', 'SWIPE'));
            $cardDataInputMode = 'MAGNETIC_STRIPE_READER_INPUT';
        }

        if (!empty($builder->amount)) {
            $transaction->appendChild($xml->createElement('transactionAmount', AmountUtils::transitFormat($builder->amount)));
        }

        if ($commercialDataSubmitted) { // has to come before card info
            $transaction->appendChild($xml->createElement('salesTax', AmountUtils::transitFormat($builder->commercialData->taxAmount)));

            $additionalTaxDetailsNode = $xml->createElement('additionalTaxDetails');

            if (!empty($builder->commercialData->additionalTaxDetails->taxType)) {
                $additionalTaxDetailsNode->appendChild($xml->createElement('taxType', $builder->commercialData->additionalTaxDetails->taxType));
            } else {
                $additionalTaxDetailsNode->appendChild($xml->createElement('taxType', $builder->commercialData->taxType));
            }

            if (!empty($builder->commercialData->additionalTaxDetails->taxAmount)) {
                $additionalTaxDetailsNode->appendChild($xml->createElement('taxAmount', AmountUtils::transitFormat($builder->commercialData->additionalTaxDetails->taxAmount)));
            } else {
                $additionalTaxDetailsNode->appendChild($xml->createElement('taxAmount', AmountUtils::transitFormat($builder->commercialData->taxAmount)));
            }

            if (!empty($builder->commercialData->additionalTaxDetails->taxRate)) {
                $additionalTaxDetailsNode->appendChild($xml->createElement('taxRate', $builder->commercialData->additionalTaxDetails->taxRate));
            }

            if (!empty($builder->commercialData->additionalTaxDetails->taxCategory)) {
                $additionalTaxDetailsNode->appendChild($xml->createElement('taxCategory', $builder->commercialData->additionalTaxDetails->taxCategory));
            }

            $transaction->appendChild($additionalTaxDetailsNode);

            if ($builder->commercialData->freightAmount) {
                $transaction->appendChild($xml->createElement('shippingCharges', AmountUtils::transitFormat($builder->commercialData->freightAmount)));
            }
            
            if ($builder->commercialData->dutyAmount) {
                $transaction->appendChild($xml->createElement('dutyCharges', AmountUtils::transitFormat($builder->commercialData->dutyAmount)));
            }
        }
    
        if ($paymentMethod instanceof CreditCardData) {
            $transaction->appendChild($xml->createElement('cardNumber', $paymentMethod->token != null ? $paymentMethod->token : $paymentMethod->number));

            if ($transaction->tagName != 'GetOnusToken') {
                $transaction->appendChild($xml->createElement('expirationDate', $paymentMethod->getShortExpiry()));

                if (!empty($paymentMethod->cvn)) {
                    $transaction->appendChild($xml->createElement('cvv2', $paymentMethod->cvn));
                }
            }
        } elseif ($paymentMethod instanceof ITrackData) {
            $trackField = ($paymentMethod->trackNumber == TrackNumber::TRACK_TWO) ? 'track2Data' : 'track1Data';
            $transaction->appendChild($xml->createElement($trackField, $paymentMethod->trackData));

            if ($paymentMethod->paymentMethodType === PaymentMethodType::DEBIT) {
                $transaction->appendChild($xml->createElement('pin', $paymentMethod->pinBlock));
                $transaction->appendChild($xml->createElement('pinKsn', $paymentMethod->encryptionData->ksn));
            }
        }

        if (!empty($builder->paymentMethod->threeDSecure)) {
            $treeDeeInfo = $builder->paymentMethod->threeDSecure;

            if (!empty($treeDeeInfo->secureCode)) {
                $transaction->appendChild($xml->createElement('secureCode', $treeDeeInfo->secureCode));
            }

            if (!empty($treeDeeInfo->authenticationType)) {
                $transaction->appendChild($xml->createElement('securityProtocol', $treeDeeInfo->authenticationType));
            }

            if (!empty($treeDeeInfo->ucafIndicator)) {
                $transaction->appendChild($xml->createElement('ucafCollectionIndicator', $treeDeeInfo->ucafIndicator));
            }

            if (!empty($treeDeeInfo->authenticationValue)) {
                $transaction->appendChild($xml->createElement('digitalPaymentCryptogram', $treeDeeInfo->authenticationValue));
            }

            if ($treeDeeInfo->getVersion() === Secure3dVersion::ONE) {
                $transaction->appendChild($xml->createElement('programProtocol', '1'));
            } elseif ($treeDeeInfo->getVersion() === Secure3dVersion::TWO) {
                $transaction->appendChild($xml->createElement('programProtocol', '2'));

                if (!empty($treeDeeInfo->directoryServerTransactionId)) {
                    $transaction->appendChild($xml->createElement('directoryServerTransactionID', $treeDeeInfo->directoryServerTransactionId));
                }
            }

            if (!empty($treeDeeInfo->eci)) {
                $transaction->appendChild($xml->createElement('eciIndicator', $treeDeeInfo->eci));
            }
        }

        if (!empty($builder->storedCredential->cardBrandTransactionId)) {
            $transaction->appendChild(($xml->createElement('cardOnFileTransactionIdentifier', $builder->storedCredential->cardBrandTransactionId)));
        }

        if ($transaction->tagName === 'GetOnusToken') { // bypass most of the fields used below since they don't apply to GetOnusToken
            $transaction->appendChild($xml->createElement('cardVerification', 'YES'));
            $transaction->appendChild($xml->createElement('developerID', $this->developerId));
            $response = $this->doTransaction($xml->saveXML($transaction));
            return $this->mapResponse($builder, $response);
        }

        if (!empty($builder->commercialData->lineItems)) {
            foreach ($builder->commercialData->lineItems as $lineItem) {
                $productDetailsNode = $xml->createElement('productDetails');

                if (!empty($lineItem->productCode)) {
                    $productDetailsNode->appendChild($xml->createElement('productCode', $lineItem->productCode));
                }

                if (!empty($lineItem->name)) {
                    $productDetailsNode->appendChild($xml->createElement('productName', $lineItem->name));
                }

                if (!empty($lineItem->unitCost)) {
                    $productDetailsNode->appendChild($xml->createElement('price', AmountUtils::transitFormat($lineItem->unitCost)));
                }

                if (!empty($lineItem->quantity)) {
                    $productDetailsNode->appendChild($xml->createElement('quantity', $lineItem->quantity));
                }

                if (!empty($lineItem->unitOfMeasure)) {
                    $productDetailsNode->appendChild($xml->createElement('measurementUnit', $lineItem->unitOfMeasure));
                }
                
                if (!empty($lineItem->discountDetails)) {
                    $productDiscountDetailsNode = $xml->createElement('productDiscountDetails');

                    if (!empty($lineItem->discountDetails->discountName)) {
                        $productDiscountDetailsNode->appendChild($xml->createElement('productDiscountName', $lineItem->discountDetails->discountName));
                    }

                    $productDiscountDetailsNode->appendChild($xml->createElement('productDiscountAmount', AmountUtils::transitFormat($lineItem->discountDetails->discountAmount)));

                    if (!empty($lineItem->discountDetails->discountPercentage)) {
                        $productDiscountDetailsNode->appendChild($xml->createElement('productDiscountPercentage', $lineItem->discountDetails->discountPercentage));
                    }

                    if (!empty($lineItem->discountDetails->discountType)) {
                        $productDiscountDetailsNode->appendChild($xml->createElement('productDiscountType', $lineItem->discountDetails->discountType));
                    }

                    if (!empty($lineItem->discountDetails->priority)) {
                        $productDiscountDetailsNode->appendChild($xml->createElement('priority', $lineItem->discountDetails->priority));
                    }

                    if (!empty($lineItem->discountDetails->stackable)) {
                        $productDiscountDetailsNode->appendChild($xml->createElement('stackable', $lineItem->discountDetails->stackable ? 'YES' : 'NO'));
                    }

                    $productDetailsNode->appendChild($productDiscountDetailsNode);
                }

                if ($lineItem->taxAmount !== null) {
                    $productTaxDetailsNode = $xml->createElement('productTaxDetails');

                    $productTaxDetailsNode->appendChild($xml->createElement('productTaxName', $lineItem->taxName));
                    $productTaxDetailsNode->appendChild($xml->createElement('productTaxAmount', AmountUtils::transitFormat($lineItem->taxAmount)));

                    if (!empty($lineItem->taxPercentage)) {
                        $productTaxDetailsNode->appendChild($xml->createElement('productTaxPercentage', $lineItem->taxPercentage));
                    }
                    
                    if (!empty($lineItem->taxType)) {
                        $productTaxDetailsNode->appendChild($xml->createElement('productTaxType'));
                    }

                    $productDetailsNode->appendChild($productTaxDetailsNode);
                }

                if (!empty($lineItem->description)) {
                    $productDetailsNode->appendChild($xml->createElement('productNotes', $lineItem->description));
                }

                if (!empty($lineItem->commodityCode)) {
                    $productDetailsNode->appendChild($xml->createElement('productCommodityCode', $lineItem->commodityCode));
                }

                if (!empty($lineItem->alternateTaxId)) {
                    $productDetailsNode->appendChild($xml->createElement('alternateTaxID', $lineItem->alternateTaxId));
                }

                if ($lineItem->creditDebitIndicator === CreditDebitIndicator::CREDIT) {
                    $productDetailsNode->appendChild($xml->createElement('creditIndicator', 'YES'));
                }

                $transaction->appendChild($productDetailsNode);
            }
        }
      
        if ($commercialDataSubmitted) {
            if ($builder->commercialData->commercialIndicator === CommercialIndicator::LEVEL_II) {
                $transaction->appendChild($xml->createElement('commercialCardLevel', 'LEVEL2'));
            } elseif ($builder->commercialData->commercialIndicator === CommercialIndicator::LEVEL_III) {
                $transaction->appendChild($xml->createElement('commercialCardLevel', 'LEVEL3'));
            }
            
            if (!empty($builder->commercialData->poNumber)) {
                $transaction->appendChild($xml->createElement('purchaseOrder', $builder->commercialData->poNumber));
            }

            if (!empty($builder->commercialData->description)) { // Amex only
                $transaction->appendChild($xml->createElement('chargeDescriptor', $builder->commercialData->description));
            }

            if (!empty($builder->commercialData->customerVatNumber)) {
                $transaction->appendChild($xml->createElement('customerVATNumber', $builder->commercialData->customerVatNumber));
            }

            if (!empty($builder->commercialData->customerReferenceId)) {
                $transaction->appendChild($xml->createElement('customerRefID', $builder->commercialData->customerReferenceId));
            }

            if (!empty($builder->commercialData->orderDate)) {
                $transaction->appendChild($xml->createElement('orderDate', $builder->commercialData->orderDate));
            }

            if (!empty($builder->commercialData->summaryCommodityCode)) {
                $transaction->appendChild($xml->createElement('summaryCommodityCode', $builder->commercialData->summaryCommodityCode));
            }

            if (!empty($builder->commercialData->vatInvoiceNumber)) {
                $transaction->appendChild($xml->createElement('vatInvoice', $builder->commercialData->vatInvoiceNumber));
            }

            if (!empty($builder->commercialData->supplierReferenceNumber)) {
                $transaction->appendChild($xml->createElement('supplierReferenceNumber', $builder->commercialData->supplierReferenceNumber));
            }

            if (!empty($builder->commercialData->originPostalCode)) {
                $transaction->appendChild($xml->createElement('shipFromZip', $builder->commercialData->originPostalCode));
            }

            if (!empty($builder->commercialData->destinationPostalCode)) {
                $transaction->appendChild($xml->createElement('shipToZip', $builder->commercialData->destinationPostalCode));
            }

            if (!empty($builder->commercialData->destinationCountryCode)) {
                $transaction->appendChild($xml->createElement('destinationCountryCode', $builder->commercialData->destinationCountryCode));
            }
        }
      
        if (!empty($builder->billingAddress) && !$commercialDataSubmitted) { // addy and commercial data are mutually exclusive
            $transaction->appendChild($xml->createElement('addressLine1', $builder->billingAddress->streetAddress1));
            $transaction->appendChild($xml->createElement('zip', $builder->billingAddress->postalCode));
        }

        if (!empty($builder->clientTransactionId)) {
            $transaction->appendChild($xml->createElement('externalReferenceID', $builder->clientTransactionId)); // required for all non Lvl2/Lvl3 trans
        }

        if ($builder->cardOnFile) {
            $transaction->appendChild($xml->createElement('cardOnFile', 'Y'));
        }

        if ($builder->requestMultiUseToken) {
            $transaction->appendChild($xml->createElement('tokenRequired', 'Y'));
        }

        if ($transaction->tagName === "CardVerification") {
            $transaction->appendChild($xml->createElement('developerID', $this->developerId));
        }
        
        if (!empty($builder->cashTendered)) {
            $transaction->appendChild($xml->createElement('cashTendered', AmountUtils::transitFormat($builder->cashTendered)));
        }

        $transaction->appendChild($xml->createElement('terminalCapability', $this->acceptorConfig->cardDataInputCapability));
        $transaction->appendChild($xml->createElement('terminalOperatingEnvironment', $this->acceptorConfig->operatingEnvironment));
        $transaction->appendChild($xml->createElement('cardholderAuthenticationMethod', 'NOT_AUTHENTICATED'));
        $transaction->appendChild($xml->createElement('terminalAuthenticationCapability', $this->acceptorConfig->cardHolderAuthenticationCapability));
        $transaction->appendChild($xml->createElement('terminalOutputCapability', $this->acceptorConfig->terminalOutputCapability));
        $transaction->appendChild($xml->createElement('maxPinLength', $this->acceptorConfig->pinCaptureCapability));
        $transaction->appendChild($xml->createElement('terminalCardCaptureCapability', $this->acceptorConfig->cardCaptureCapability ? 'CARD_CAPTURE_CAPABILITY' : 'NO_CAPABILITY'));

        if ($paymentMethod->cardPresent) {
            $cardHolderPresentDetailValue = 'CARDHOLDER_PRESENT';
        } else {
            if ($this->acceptorConfig->cardDataSource === CardDataSource::MAIL) {
                $cardHolderPresentDetailValue = 'CARDHOLDER_NOT_PRESENT_MAIL_TRANSACTION';
            } elseif ($this->acceptorConfig->cardDataSource === CardDataSource::PHONE) {
                $cardHolderPresentDetailValue = 'CARDHOLDER_NOT_PRESENT_PHONE_TRANSACTION';
            } else {
                $cardHolderPresentDetailValue = 'CARDHOLDER_NOT_PRESENT_ELECTRONIC_COMMERCE';
            }
        }

        $transaction->appendChild($xml->createElement('cardholderPresentDetail', $cardHolderPresentDetailValue));

        if ($paymentMethod instanceof ITrackData || $paymentMethod->cardPresent) {
            $transaction->appendChild($xml->createElement('cardPresentDetail', 'CARD_PRESENT'));
        } else {
            $transaction->appendChild($xml->createElement('cardPresentDetail', 'CARD_NOT_PRESENT'));
        }

        if (!empty($builder->storedCredential)) {
            if ($builder->storedCredential->initiator === StoredCredentialInitiator::MERCHANT) {
                $transaction->appendChild($xml->createElement('cardDataInputMode', 'MERCHANT_INITIATED_TRANSACTION_CARD_CREDENTIAL_STORED_ON_FILE'));
            }
        } else {
            $transaction->appendChild($xml->createElement('cardDataInputMode', $cardDataInputMode));
        }
        
        $transaction->appendChild($xml->createElement('cardholderAuthenticationEntity', $this->acceptorConfig->cardHolderAuthenticationEntity));
        $transaction->appendChild($xml->createElement('cardDataOutputCapability', $this->acceptorConfig->cardDataOutputCapability));

        if ($transaction->tagName != "CardVerification") {
            $transaction->appendChild($xml->createElement('developerID', $this->developerId));
        }

        if ($paymentMethod->cardType === CardType::DISCOVER && ($this->acceptorConfig->cardDataSource === CardDataSource::INTERNET || empty($this->acceptorConfig->cardDataSource))) {
            if (!empty($builder->lastRegisteredDate)) {
                $transaction->appendChild($xml->createElement('registeredUserIndicator', 'YES'));
                $transaction->appendChild($xml->createElement('lastRegisteredChangeDate', $builder->lastRegisteredDate));
            } else {
                $transaction->appendChild($xml->createElement('registeredUserIndicator', 'NO'));
                $transaction->appendChild($xml->createElement('lastRegisteredChangeDate', '00/00/0000'));
            }
        }

        if ($paymentMethod->cardType === CardType::MASTERCARD && $transaction->tagName != "CardVerification") {
            if (!empty($builder->AmountEstimated)) {
                $transaction->appendChild($xml->createElement('authorizationIndicator', $builder->AmountEstimated ? "PREAUTH" : "FINAL"));
            } else {
                $transaction->appendChild($xml->createElement('authorizationIndicator', 'FINAL'));
            }
        }

        $response = $this->doTransaction($xml->saveXML($transaction));
        return $this->mapResponse($builder, $response);
    }

    public function manageTransaction(ManagementBuilder $builder)
    {
        if (empty($this->transactionKey) && empty($this->manifest)) {
            throw new ConfigurationException('transactionKey/manifest is required for this transaction.');
        }
        
        $xml = new DOMDocument();

        $paymentMethod = $builder->paymentMethod;

        $transaction = $xml->createElement($this->mapRequestType($builder));
        $transaction->appendChild($xml->createElement('deviceID', $this->deviceId));
        $transaction->appendChild($xml->createElement('transactionKey', $this->transactionKey));

        if (!empty($builder->amount)) {
            $transaction->appendChild($xml->createElement('transactionAmount', AmountUtils::transitFormat($builder->amount)));
        }
        
        if (!empty($builder->gratuity)) {
            $transaction->appendChild($xml->createElement('tip', AmountUtils::transitFormat($builder->gratuity)));
        }
        
        if (!empty($paymentMethod->transactionId)) {
            $transaction->appendChild($xml->createElement('transactionID', $paymentMethod->transactionId));
        }

        if ($builder->multiCapture) {
            $transaction->appendChild($xml->createElement('isPartialShipment', 'Y'));

            $partialShipmentDataNode = $xml->createElement('partialShipmentData');

            if ($builder->multiCaptureSequence < 10) {
                $builder->multiCaptureSequence =  '0' . strval($builder->multiCaptureSequence);
            }

            if ($builder->multiCapturePaymentCount < 10) {
                $builder->multiCapturePaymentCount =  '0' . strval($builder->multiCapturePaymentCount);
            }

            $partialShipmentDataNode->appendChild($xml->createElement('currentPaymentSequenceNumber', $builder->multiCaptureSequence));
            $partialShipmentDataNode->appendChild($xml->createElement('totalPaymentCount', $builder->multiCapturePaymentCount));
            
            $transaction->appendChild($partialShipmentDataNode);
        }
        
        if ($builder->transactionType === TransactionType::BATCH_CLOSE) {
            $transaction->appendChild($xml->createElement('operatingUserID', $this->userId));
        } else {
            $transaction->appendChild($xml->createElement('developerID', $this->developerId));
        }
        
        if (!empty($builder->description) && $builder->transactionType == TransactionType::VOID) {
            $transaction->appendChild($xml->createElement('voidReason', $builder->description));
        }
        
        $response = $this->doTransaction($xml->saveXML($transaction));
        return $this->mapResponse($builder, $response);
    }

    public function serializeRequest(AuthorizationBuilder $builder)
    {
        throw new UnsupportedTransactionException();
    }

    public function mapRequestType(TransactionBuilder $builder)
    {
        switch ($builder->transactionType) {
            case TransactionType::AUTH:
                return 'Auth';
            case TransactionType::CAPTURE:
                return 'Capture';
            case TransactionType::SALE:
                if ($builder->paymentMethod->paymentMethodType === PaymentMethodType::DEBIT) {
                    return 'DebitSale';
                } elseif ($builder->paymentMethod->paymentMethodType === PaymentMethodType::CASH) {
                    return 'CashSale';
                }
                return 'Sale';
            case TransactionType::BALANCE:
                return 'BalanceInquiry';
            case TransactionType::VERIFY:
                if ($builder->requestMultiUseToken === true) {
                    return 'GetOnusToken';
                } else {
                    return 'CardVerification';
                }
            case TransactionType::EDIT:
                return 'TipAdjustment';
            case TransactionType::VOID:
                return 'Void';
            case TransactionType::BATCH_CLOSE:
                return 'BatchClose';
            case TransactionType::REFUND:
                return 'Return';
            default:
                throw new UnsupportedTransactionException();
        }
    }

    public function mapResponse($builder, $rawResponse)
    {
        $root = $this->xml2object($rawResponse);
                
        $this->checkResponse($root);

        $response = new Transaction();
        $response->responseCode = '00';
        $response->responseMessage = (string) $root->responseMessage;
        $response->transactionId = (string) $root->transactionID;
        $response->hostResponseDate = (string) $root->transactionTimestamp;
        $response->authorizedAmount = (string) $root->transactionAmount;
        $response->avsResponseCode = (string) $root->addressVerificationCode;
        $response->cardType = (string) $root->cardType;
        $response->cardLast4 = (string) $root->maskedCardNumber;
        $response->commercialIndicator = (string) $root->commercialCard;
        $response->customerReceipt = (string) $root->customerReceipt;
        $response->merchantReceipt = (string) $root->merchantReceipt;
        $response->token = (string)$root->token;
        $response->authorizationCode = (string)$root->authCode;
        $response->transactionKey = (string)$root->transactionKey;
        $response->cardBrandTransactionId = (string)$root->cardTransactionIdentifier;
        
        if (!empty($builder) && $builder->transactionType === TransactionType::BATCH_CLOSE) {
            $response->batchSummary = new BatchSummary();
            $response->batchSummary->totalAmount = (string)$root->batchInfo->saleAmount;
            $response->batchSummary->transactionCount = (string)$root->batchInfo->saleCount;
        }

        return $response;
    }

    /**
     * Converts a XML string to a simple object for use,
     * removing extra nodes that are not necessary for
     * handling the response
     *
     * @param string $xml Response XML from the gateway
     *
     * @return SimpleXMLElement
     */
    protected function xml2object($xml)
    {
        $envelope = simplexml_load_string(
            $xml,
            'SimpleXMLElement'
        );

        return $envelope;
    }

    public function processReport(ReportBuilder $builder)
    {
    }

    protected function checkResponse($root)
    {
        $acceptedCodes = [ '00', 'A0000' ];

        $responseCode = (string)$root->hostResponseCode;
        $responseMessage = (string)$root->responseMessage;
        $status = (string)$root->status;

        if (!in_array($responseCode, $acceptedCodes) && $status !== 'PASS') {
            throw new GatewayException(
                sprintf('Unexpected Gateway Response: %s - %s', $responseCode, $responseMessage),
                $responseCode,
                $responseMessage
            );
        }
    }
    
    public function getTransactionKey()
    {
        $xml = new DOMDocument();
        
        $transaction = $xml->createElement('GenerateKey');
        $transaction->appendChild($xml->createElement('mid', $this->merchantId));
        $transaction->appendChild($xml->createElement('userID', $this->userId));
        $transaction->appendChild($xml->createElement('password', $this->password));
        
        if (!empty($this->transactionKey)) {
            $transaction->appendChild($xml->createElement('transactionKey', $this->transactionKey));
        }
        
        $response = $this->doTransaction($xml->saveXML($transaction));
        return $this->mapResponse(null, $response);
    }
    
    public function createManifest()
    {
        $sEncryptedData = "";
        $now = new \DateTime();
        $dateFormatString = $now->format('mdY');
        $plainText = StringUtils::asPaddedAtEndString($this->merchantId, 20, ' ')
                . StringUtils::asPaddedAtEndString($this->deviceId, 24, ' ')
                . '000000000000'
                . StringUtils::asPaddedAtEndString($dateFormatString, 8, ' ');
        $tempTransactionKey = substr($this->transactionKey, 0, 16);
        $encrypted = openssl_encrypt(
            $plainText,
            'aes-128-cbc',
            $tempTransactionKey,
            OPENSSL_ZERO_PADDING,
            $tempTransactionKey
        );
        $sEncryptedData = bin2hex(base64_decode($encrypted));
        $hashKey = hash_hmac('md5', $this->transactionKey, $this->transactionKey);
        return substr($hashKey, 0, 4) . $sEncryptedData . substr($hashKey, -4, 4);
    }
}
