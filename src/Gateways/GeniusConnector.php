<?php

namespace GlobalPayments\Api\Gateways;

use DOMDocument;
use DOMElement;
use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Builders\BaseBuilder;
use GlobalPayments\Api\Builders\ManagementBuilder;
use GlobalPayments\Api\Builders\ReportBuilder;
use GlobalPayments\Api\Builders\TransactionBuilder;
use GlobalPayments\Api\Entities\BatchSummary;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\Entities\Enums\AccountType;
use GlobalPayments\Api\Entities\Enums\AliasAction;
use GlobalPayments\Api\Entities\Enums\CheckType;
use GlobalPayments\Api\Entities\Enums\EntryMethod;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\TaxType;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException;
use GlobalPayments\Api\Entities\Reporting\CheckData;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\ECheck;
use GlobalPayments\Api\PaymentMethods\GiftCard;
use GlobalPayments\Api\PaymentMethods\Interfaces\IBalanceable;
use GlobalPayments\Api\PaymentMethods\Interfaces\ICardData;
use GlobalPayments\Api\PaymentMethods\Interfaces\IEncryptable;
use GlobalPayments\Api\PaymentMethods\Interfaces\IPaymentMethod;
use GlobalPayments\Api\PaymentMethods\Interfaces\IPinProtected;
use GlobalPayments\Api\PaymentMethods\Interfaces\ITokenizable;
use GlobalPayments\Api\PaymentMethods\Interfaces\ITrackData;
use GlobalPayments\Api\PaymentMethods\RecurringPaymentMethod;
use GlobalPayments\Api\PaymentMethods\TransactionReference;
use GlobalPayments\Api\Entities\Enums\ReportType;
use GlobalPayments\Api\Entities\Reporting\TransactionSummary;
use GlobalPayments\Api\Entities\Reporting\SearchCriteria;
use GlobalPayments\Api\Entities\Reporting\SearchCriteriaBuilder;
use GlobalPayments\Api\PaymentMethods\CreditTrackData;
use GlobalPayments\Api\Services\ReportingService;

class GeniusConnector extends XmlGateway implements IPaymentGateway
{
    /**
     * Portico's XML namespace
     *
     * @var string
     */
    public $merchantName;
    public $merchantSiteId;
    public $merchantKey;
    public $registerNumber;
    public $terminalId;

    public $supportsHostedPayments = false;
    private $xmlNamespace = 'http://schemas.merchantwarehouse.com/merchantware/v45/';
    
    const CREDIT_SERVICE_END_POINT = 'RetailTransaction/v45/Credit.asmx';
    const GIFT_SERVICE_END_POINT = 'ExtensionServices/v46/Giftcard.asmx';

    public function processAuthorization($builder)
    {
        $xml = new DOMDocument();
        $paymentMethod = $builder->paymentMethod;
        
        $this->setGatewayParams($paymentMethod);

        $transaction = $xml->createElement($this->mapRequestType($builder));
        $transaction->setAttribute('xmlns', $this->xmlNamespace);

        // Credentials
        $credentials = $xml->createElement('Credentials');
        $credentials->appendChild($xml->createElement('MerchantName', $this->merchantName));
        $credentials->appendChild($xml->createElement('MerchantSiteId', $this->merchantSiteId));
        $credentials->appendChild($xml->createElement('MerchantKey', $this->merchantKey));
        
        $transaction->appendChild($credentials);

        // Payment Data
        $paymentData = $xml->createElement('PaymentData');
        $this->hydratePaymentData($xml, $paymentData, $paymentMethod);

        // AVS
        if (!empty($builder->billingAddress)) {
            $paymentData->appendChild($xml->createElement(
                'AvsStreetAddress',
                $builder->billingAddress->streetAddress1
            ));
            $paymentData->appendChild($xml->createElement('AvsZipCode', $builder->billingAddress->postalCode));
        }

        $transaction->appendChild($paymentData);

        // Request
        $request = $xml->createElement('Request');
        if ($paymentMethod->paymentMethodType === PaymentMethodType::GIFT && !empty($builder->currency)) {
            $request->appendChild($xml->createElement('AmountType', $builder->currency));
        }
        $request->appendChild($xml->createElement('Amount', $builder->amount));
        $request->appendChild($xml->createElement('CashbackAmount', $builder->cashBackAmount));
        $request->appendChild($xml->createElement('SurchargeAmount', $builder->convenienceAmount));
        $request->appendChild($xml->createElement('AuthorizationCode', $builder->offlineAuthCode));

        if ($builder->autoSubstantiation != null) {
            $healthcare = $xml->createElement('HealthCareAmountDetails');

            $auto = $builder->autoSubstantiation;
            $healthcare->appendChild($xml->createElement('CopayAmount', $auto->getCopaySubTotal()));
            $healthcare->appendChild($xml->createElement('ClinicalAmount', $auto->getClinicSubTotal()));
            $healthcare->appendChild($xml->createElement('DentalAmount', $auto->getDentalSubTotal()));
            $healthcare->appendChild($xml->createElement('HealthCareTotalAmount', $auto->getTotalHealthcareAmount()));
            $healthcare->appendChild($xml->createElement('PrescriptionAmount', $auto->getPrescriptionSubTotal()));
            $healthcare->appendChild($xml->createElement('VisionAmount', $auto->getVisionSubTotal()));
            
            $request->appendChild($healthcare);
        }

        $request->appendChild($xml->createElement('InvoiceNumber', $builder->invoiceNumber));
        $request->appendChild($xml->createElement('RegisterNumber', $this->registerNumber));
        $request->appendChild($xml->createElement('MerchantTransactionId', $builder->clientTransactionId));
        $request->appendChild($xml->createElement('CardAcceptorTerminalId', $this->terminalId));
        // invoice object
        $request->appendChild($xml->createElement('EnablePartialAuthorization', $builder->allowPartialAuth));
        $request->appendChild($xml->createElement('ForceDuplicate', $builder->allowDuplicates));

        $transaction->appendChild($request);
        $response = $this->doTransaction($this->buildEnvelope($xml, $transaction));
        return $this->mapResponse($builder, $response);
    }

    public function manageTransaction($builder)
    {
        $xml = new DOMDocument();
        $transactionType = $builder->transactionType;
        $this->setGatewayParams($builder->paymentMethod);

        $transaction = $xml->createElement($this->mapRequestType($builder));
        $transaction->setAttribute('xmlns', $this->xmlNamespace);

        // Credentials
        $credentials = $xml->createElement('Credentials');
        $credentials->appendChild($xml->createElement('MerchantName', $this->merchantName));
        $credentials->appendChild($xml->createElement('MerchantSiteId', $this->merchantSiteId));
        $credentials->appendChild($xml->createElement('MerchantKey', $this->merchantKey));
        
        $transaction->appendChild($credentials);

        // Payment Data
        if ($transactionType === TransactionType::REFUND) {
            $paymentData = $xml->createElement('PaymentData');

            $paymentData->appendChild($xml->createElement('Source', 'PreviousTransaction'));
            $paymentData->appendChild($xml->createElement('Token', $builder->transactionId));

            $transaction->appendChild($paymentData);
        }

        // Request
        $request = $xml->createElement('Request');
        if ($transactionType !== TransactionType::REFUND) {
            $request->appendChild($xml->createElement('Token', $builder->transactionId));
        }
        $request->appendChild($xml->createElement('Amount', $builder->amount + $builder->gratuity));
        if (!empty($builder->invoiceNumber)) {
            $request->appendChild($xml->createElement('InvoiceNumber', $builder->invoiceNumber));
        }
        if (!empty($builder->registerNumber)) {
            $request->appendChild($xml->createElement('RegisterNumber', $this->registerNumber));
        }
        if (!empty($builder->clientTransactionId)) {
            $request->appendChild($xml->createElement('MerchantTransactionId', $builder->clientTransactionId));
        }
        if (!empty($builder->terminalId)) {
            $request->appendChild($xml->createElement('CardAcceptorTerminalId', $this->terminalId));
        }

        if ($transactionType === TransactionType::TOKEN_DELETE || $transactionType === TransactionType::TOKEN_UPDATE) {
            $card = $builder->paymentMethod;

            $request->appendChild($xml->createElement('VaultToken', $card->token));
            if ($transactionType === TransactionType::TOKEN_UPDATE) {
                $request->appendChild($xml->createElement('ExpirationDate', $card->getShortExpiry()));
            }
        }

        $transaction->appendChild($request);
    
        $response = $this->doTransaction($this->buildEnvelope($xml, $transaction));
        return $this->mapResponse($builder, $response);
    }

    public function serializeRequest($builder)
    {
        throw new UnsupportedTransactionException();
    }

    public function buildEnvelope(DOMDocument $xml, DOMElement $transaction)
    {
        $soapEnvelope = $xml->createElement('soapenv:Envelope');
        $soapEnvelope->setAttribute(
            'xmlns:soapenv',
            'http://schemas.xmlsoap.org/soap/envelope/'
        );
        $soapEnvelope->setAttribute('xmlns', $this->xmlNamespace);

        $soapBody = $xml->createElement('soapenv:Body');

        $soapBody->appendChild($transaction);
        $soapEnvelope->appendChild($soapBody);
        $xml->appendChild($soapEnvelope);

        return $xml->saveXML();
    }

    public function mapRequestType(TransactionBuilder $builder)
    {
        switch ($builder->transactionType) {
            case TransactionType::AUTH:
                if ($builder->transactionModifier === TransactionModifier::OFFLINE) {
                    return 'ForceCapture';
                }
                return 'Authorize';
            case TransactionType::BATCH_CLOSE:
                return 'SettleBatch';
            case TransactionType::CAPTURE:
                return 'Capture';
            case TransactionType::EDIT:
                return 'AdjustTip';
            case TransactionType::REFUND:
                return 'Refund';
            case TransactionType::SALE:
                return 'Sale';
            case TransactionType::TOKEN_DELETE:
                return 'UnboardCard';
            case TransactionType::TOKEN_UPDATE:
                return 'UpdateBoardedCard';
            case TransactionType::VERIFY:
                return 'BoardCard';
            case TransactionType::VOID:
                return 'Void';
            case TransactionType::BALANCE:
                return 'BalanceInquiry';
            case TransactionType::ADD_VALUE:
                return 'AddValue';
            case TransactionType::ACTIVATE:
                return 'ActivateCard';
            case TransactionType::REWARD:
                return 'AddPoints';
            default:
                throw new UnsupportedTransactionException();
        }
    }

    public function mapWalletId($mobileType)
    {
        switch ($mobileType) {
            case 'apple-pay':
                return 'ApplePay';
            default:
                return 'Unknown';
        }
    }

    public function mapResponse($builder, $rawResponse)
    {
        $root = $this->xml2object($rawResponse);
        
        $item = $root->{$this->mapRequestType($builder).'Result'};
        
        $errorCode = (string) $item->ErrorCode;
        $errorMessage = (string) $item->ErrorMessage;
        
        if (!empty($errorMessage)) {
            throw new GatewayException(
                sprintf(
                    'Unexpected Gateway Response: %s - %s. ',
                    $errorCode,
                    $errorMessage
                )
            );
        }
        
        $response = new Transaction();

        $response->responseCode = '00';
        $response->responseMessage = (string)$item->ApprovalStatus;
        $response->transactionId = (string)$item->Token;
        $response->authorizationCode = (string)$item->AuthorizationCode;
        $response->hostResponseDate = (string)$item->TransactionDate;
        $response->authorizedAmount = (string)$item->Amount;
        $response->availableBalance = (string)$item->RemainingCardBalance;
        $response->cardType = (string)$item->CardType;
        $response->avsResponseCode = (string)$item->AvsResponse;
        $response->cvnResponseCode = (string)$item->CvResponse;
        $response->token = (string)$item->VaultToken;

        if (isset($item->BatchStatus)) {
            $response->batchSummary = new BatchSummary();
            $response->batchSummary->status = (string)$item->BatchStatus;
            $response->batchSummary->totalAmount = (string)$item->BatchAmount;
            $response->batchSummary->transactionCount = (string)$item->TransactionCount;
        }
        
        if (isset($item->Gift)) {
            $response->authorizedAmount = (string)$item->Gift->ApprovedAmount;
            $response->balanceAmount = (string)$item->Gift->RedeemableBalance;
        }
        
        if (isset($item->Loyalty)) {
            $response->pointsBalanceAmount = (string)$item->Loyalty->PointsBalance;
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
            'SimpleXMLElement',
            0,
            'http://schemas.xmlsoap.org/soap/envelope/'
        );

        foreach ($envelope->Body as $response) {
            $children = $response->children($this->xmlNamespace);
            foreach ($children as $item) {
                return $item;
            }
        }

        throw new Exception('XML from gateway could not be parsed');
    }

    public function processReport($builder)
    {
    }
    
    private function setGatewayParams($paymentMethod)
    {
        if (!empty($paymentMethod->paymentMethodType) &&
            $paymentMethod->paymentMethodType === PaymentMethodType::GIFT) {
                $this->xmlNamespace = 'http://schemas.merchantwarehouse.com/merchantware/46/Giftcard';
                $this->serviceUrl .= self::GIFT_SERVICE_END_POINT;
        } else {
            $this->serviceUrl .= self::CREDIT_SERVICE_END_POINT;
        }
    }
    
    private function hydratePaymentData($xml, $paymentData, $paymentMethod)
    {
        if ($paymentMethod->paymentMethodType === PaymentMethodType::GIFT) {
            $card = $paymentMethod;
            if ($card->valueType === 'CardNbr') {
                $paymentData->appendChild($xml->createElement('Source', 'Keyed'));
                $paymentData->appendChild($xml->createElement('CardNumber', $card->number));
                $paymentData->appendChild($xml->createElement('GiftCardPin', $card->pin));
            } elseif ($card->valueType === 'TrackData') {
                $paymentData->appendChild($xml->createElement('Source', 'READER'));
                $paymentData->appendChild($xml->createElement('TrackData', $card->value));
            }
        } else {
            if ($paymentMethod instanceof CreditCardData) {
                $card = $paymentMethod;
                
                if (!empty($card->token)) {
                    if (!empty($card->mobileType)) {
                        $paymentData->appendChild($xml->createElement('Source', 'Wallet'));
                        $paymentData->appendChild($xml->createElement(
                            'WalletId',
                            $this->mapWalletId($card->mobileType)
                        ));
                        $paymentData->appendChild($xml->createElement('EncryptedPaymentData', $card->token));
                    } else {
                        $paymentData->appendChild($xml->createElement('Source', 'Vault'));
                        $paymentData->appendChild($xml->createElement('VaultToken', $card->token));
                    }
                } else {
                    $paymentData->appendChild($xml->createElement('Source', 'Keyed'));
                    $paymentData->appendChild($xml->createElement('CardNumber', $card->number));
                    $paymentData->appendChild($xml->createElement('ExpirationDate', $card->getShortExpiry()));
                    $paymentData->appendChild($xml->createElement('CardHolder', $card->cardHolderName));
                    $paymentData->appendChild($xml->createElement('CardVerificationValue', $card->cvn));
                }
            } elseif ($paymentMethod instanceof CreditTrackData) {
                $paymentData->appendChild($xml->createElement('Source', 'READER'));
                
                $track = $paymentMethod;
                $paymentData->appendChild($xml->createElement('TrackData', $track->value));
            }
        }
    }
}
