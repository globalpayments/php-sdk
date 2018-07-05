<?php

namespace GlobalPayments\Api\Gateways;

use DOMDocument;
use DOMElement;
use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Builders\BaseBuilder;
use GlobalPayments\Api\Builders\ManagementBuilder;
use GlobalPayments\Api\Builders\ReportBuilder;
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
use GlobalPayments\Api\PaymentMethods\ECheck;
use GlobalPayments\Api\PaymentMethods\GiftCard;
use GlobalPayments\Api\PaymentMethods\Interfaces\IBalanceable;
use GlobalPayments\Api\PaymentMethods\Interfaces\ICardData;
use GlobalPayments\Api\PaymentMethods\Interfaces\IEncryptable;
use GlobalPayments\Api\PaymentMethods\Interfaces\IPaymentMethod;
use GlobalPayments\Api\PaymentMethods\Interfaces\IPinProtected;
use GlobalPayments\Api\PaymentMethods\Interfaces\ITokenizable;
use GlobalPayments\Api\PaymentMethods\Interfaces\ITrackData;
use GlobalPayments\Api\PaymentMethods\TransactionReference;

class PorticoConnector extends XmlGateway implements IPaymentGateway
{
    /**
     * Portico's XML namespace
     *
     * @var string
     */
    const XML_NAMESPACE = 'http://Hps.Exchange.PosGateway';

    /**
     * Site ID to authenticate with the gateway
     *
     * @var string
     */
    public $siteId;

    /**
     * License ID to authenticate with the gateway
     *
     * @var string
     */
    public $licenseId;

    /**
     * Device ID to authenticate with the gateway
     *
     * @var string
     */
    public $deviceId;

    /**
     * Username to authenticate with the gateway
     *
     * @var string
     */
    public $username;

    /**
     * Password to authenticate with the gateway
     *
     * @var string
     */
    public $password;

    /**
     * Secret API Key to authenticate with the gateway.
     *
     * This can be used in place of the following properties:
     *
     * - username
     * - password
     * - siteId
     * - licenseId
     * - deviceId
     *
     * @var string
     */
    public $secretApiKey;

    /**
     * Developer ID for the application, as given during certification
     *
     * @var string
     */
    public $developerId;

    /**
     * Version number for the application, as given during certification
     *
     * @var string
     */
    public $versionNumber;

    public $supportsHostedPayments = false;

    /**
     * {@inheritdoc}
     *
     * @param AuthorizationBuilder $builder The transaction's builder
     *
     * @return Transaction
     */
    public function processAuthorization(AuthorizationBuilder $builder)
    {
        $xml = new DOMDocument('1.0', 'utf-8');

        $transaction = $xml->createElement($this->mapRequestType($builder));
        $block1 = $xml->createElement('Block1');

        if ($builder->paymentMethod->paymentMethodType !== PaymentMethodType::GIFT
            && $builder->paymentMethod->paymentMethodType !== PaymentMethodType::ACH
            && ($builder->transactionType === TransactionType::AUTH
                || $builder->transactionType === TransactionType::SALE)
        ) {
            if ($builder->paymentMethod->paymentMethodType !== PaymentMethodType::RECURRING
                || $builder->paymentMethod->paymentType !== 'ACH'
            ) {
                $block1->appendChild(
                    $xml->createElement(
                        'AllowDup',
                        ($builder->allowDuplicates ? 'Y' : 'N')
                    )
                );
            }

            if ($builder->transactionModifier === TransactionModifier::NONE
                && $builder->paymentMethod->paymentMethodType !== PaymentMethodType::EBT
                && $builder->paymentMethod->paymentMethodType !== PaymentMethodType::RECURRING
            ) {
                $block1->appendChild(
                    $xml->createElement(
                        'AllowPartialAuth',
                        ($builder->allowPartialAuth ? 'Y' : 'N')
                    )
                );
            }
        }

        if ($builder->amount !== null) {
            $block1->appendChild($xml->createElement('Amt', $builder->amount));
        }

        if ($builder->gratuity !== null) {
            $block1->appendChild(
                $xml->createElement('GratuityAmtInfo', $builder->gratuity)
            );
        }

        if ($builder->convenienceAmount !== null) {
            $block1->appendChild($xml->createElement('ConvenienceAmtInfo', $builder->convenienceAmount));
        }

        if ($builder->shippingAmount !== null) {
            $block1->appendChild($xml->createElement('ShippingAmtInfo', $builder->shippingAmount));
        }

        if ($builder->cashBackAmount !== null) {
            $block1->appendChild(
                $xml->createElement(
                    $builder->paymentMethod->paymentMethodType === PaymentMethodType::DEBIT
                        ? 'CashbackAmtInfo'
                        : 'CashBackAmount',
                    $builder->cashBackAmount
                )
            );
        }

        if ($builder->offlineAuthCode !== null) {
            $block1->appendChild(
                $xml->createElement('OfflineAuthCode', $builder->offlineAuthCode)
            );
        }

        if ($builder->transactionType === TransactionType::ALIAS) {
            $block1->appendChild($xml->createElement('Action', AliasAction::validate($builder->aliasAction)));
            $block1->appendChild($xml->createElement('Alias', $builder->alias));
        }

        $isCheck = $builder->paymentMethod->paymentMethodType === PaymentMethodType::ACH;
        if ($isCheck || $builder->billingAddress !== null) {
            $address = $this->hydrateHolder($xml, $builder, $isCheck);

            if (!empty($address)) {
                $block1->appendChild($address);
            }
        }

        list($hasToken, $tokenValue) = $this->hasToken($builder->paymentMethod);

        $cardData = $xml->createElement(
            $builder->transactionType === TransactionType::REPLACE ? 'OldCardData' : 'CardData'
        );
        if ($builder->paymentMethod instanceof ICardData) {
            $cardData->appendChild(
                $this->hydrateManualEntry(
                    $xml,
                    $builder,
                    $hasToken,
                    $tokenValue
                )
            );
        } elseif ($builder->paymentMethod instanceof ITrackData) {
            $trackData = $this->hydrateTrackData(
                $xml,
                $builder,
                $hasToken,
                $tokenValue
            );

            if ($builder->paymentMethod->paymentMethodType === PaymentMethodType::DEBIT) {
                $block1->appendChild($trackData);
            } else {
                $cardData->appendChild($trackData);
            }
        } elseif ($builder->paymentMethod instanceof GiftCard) {
            if ($builder->currency !== null) {
                $block1->appendChild($xml->createElement('Currency', strtoupper($builder->currency)));
            }

            if ($builder->transactionType === TransactionType::REPLACE) {
                $newCard = $xml->createElement('NewCardData');
                $newCard->appendChild(
                    $xml->createElement(
                        $builder->replacementCard->valueType,
                        $builder->replacementCard->value
                    )
                );

                if ($builder->replacementCard->pin !== null) {
                    $newCard->appendChild(
                        $xml->createElement(
                            'PIN',
                            $builder->replacementCard->pin
                        )
                    );
                }
                $block1->appendChild($newCard);
            }

            if ($builder->paymentMethod->value !== null) {
                $cardData->appendChild(
                    $xml->createElement(
                        $builder->paymentMethod->valueType,
                        $builder->paymentMethod->value
                    )
                );
            }

            if ($builder->paymentMethod->pin !== null) {
                $cardData->appendChild(
                    $xml->createElement(
                        'PIN',
                        $builder->paymentMethod->pin
                    )
                );
            }
        } elseif ($builder->paymentMethod instanceof ECheck) {
            $block1->appendChild($xml->createElement('CheckAction', 'SALE'));

            if (empty($builder->paymentMethod->token)) {
                $accountInfo = $xml->createElement('AccountInfo');
                $accountInfo->appendChild($xml->createElement('RoutingNumber', $builder->paymentMethod->routingNumber));
                $accountInfo->appendChild($xml->createElement('AccountNumber', $builder->paymentMethod->accountNumber));
                $accountInfo->appendChild($xml->createElement('CheckNumber', $builder->paymentMethod->checkNumber));
                $accountInfo->appendChild($xml->createElement('MICRData', $builder->paymentMethod->micrNumber));
                $accountInfo->appendChild(
                    $xml->createElement(
                        'AccountType',
                        $this->hydrateAccountType($builder->paymentMethod->accountType)
                    )
                );
                $block1->appendChild($accountInfo);
            } else {
                $block1->appendChild($xml->createElement('TokenValue', $builder->paymentMethod->token));
            }

            $block1->appendChild(
                $xml->createElement(
                    'DataEntryMode',
                    strtoupper($this->hydrateEntryMethod($builder->paymentMethod->entryMode))
                )
            );
            $block1->appendChild(
                $xml->createElement(
                    'CheckType',
                    $this->hydrateCheckType($builder->paymentMethod->checkType)
                )
            );
            $block1->appendChild($xml->createElement('SECCode', $builder->paymentMethod->secCode));

            $verify = $xml->createElement('VerifyInfo');
            $verify->appendChild(
                $xml->createElement(
                    'CheckVerify',
                    ($builder->paymentMethod->checkVerify ? 'Y' : 'N')
                )
            );
            $verify->appendChild(
                $xml->createElement(
                    'ACHVerify',
                    ($builder->paymentMethod->achVerify ? 'Y' : 'N')
                )
            );
            $block1->appendChild($verify);
        }

        if ($builder->paymentMethod instanceof TransactionReference) {
            $block1->appendChild($xml->createElement('GatewayTxnId', $builder->paymentMethod->transactionId));
            $block1->appendChild($xml->createElement('ClientTxnId', $builder->paymentMethod->clientTransactionId));
        }

        if ($builder->paymentMethod instanceof RecurringPaymentMethod) {
            $method = $builder->paymentMethod;

            if ($method->paymentType === 'ACH') {
                $block1->appendChild($xml->createElement('CheckAction', 'SALE'));
            }

            $block1->appendChild($xml->createElement('PaymentMethodKey', $method->key));

            if ($method->paymentMethod !== null && $method->paymentMethod instanceof CreditCardData) {
                $data = $xml->createElement('PaymentMethodKeyData');

                if ($method->paymentMethod->expMonth !== null) {
                    $data->appendChild($xml->createElement('ExpMonth', $method->paymentMethod->expMonth));
                }

                if ($method->paymentMethod->expYear !== null) {
                    $data->appendChild($xml->createElement('ExpYear', $method->paymentMethod->expYear));
                }

                if ($method->paymentMethod->cvn !== null) {
                    $data->appendChild($xml->createElement('CVV2', $method->paymentMethod->cvn));
                }

                $block1->appendChild($data);
            }

            $data = $xml->createElement('RecurringData');
            if ($builder->scheduleId !== null) {
                $data->appendChild($xml->createElement('ScheduleID', $builder->scheduleId));
            }
            $data->appendChild($xml->createElement('OneTime', $builder->oneTimePayment ? 'Y' : 'N'));
            $block1->appendChild($data);
        }

        if ($builder->paymentMethod instanceof IPinProtected
            && $builder->transactionType !== TransactionType::REVERSAL
        ) {
            $block1->appendChild($xml->createElement('PinBlock', $builder->paymentMethod->pinBlock));
        }

        if ($builder->paymentMethod instanceof IEncryptable
            && isset($builder->paymentMethod->encryptionData)
            && null !== $builder->paymentMethod->encryptionData
        ) {
            $enc = $this->hydrateEncryptionData($xml, $builder);

            if ($builder->paymentMethod->paymentMethodType === PaymentMethodType::DEBIT) {
                $block1->appendChild($enc);
            } else {
                $cardData->appendChild($enc);
            }
        }

        if ($builder->paymentMethod instanceof ITokenizable) {
            $cardData->appendChild(
                $xml->createElement(
                    'TokenRequest',
                    $builder->requestMultiUseToken ? 'Y' : 'N'
                )
            );
        }

        if ($cardData->childNodes->length > 0 && $builder->aliasAction !== AliasAction::CREATE) {
            $block1->appendChild($cardData);
        }

        if ($builder->paymentMethod instanceof IBalanceable && $builder->balanceInquiryType !== null) {
            $block1->appendChild($xml->createElement('BalanceInquiryType', $builder->balanceInquiryType));
        }

        if ($builder->level2Request === true) {
            $block1->appendChild($xml->createElement('CPCReq', 'Y'));
        }

        if ($builder->customerId !== null
            || $builder->description !== null
            || $builder->invoiceNumber !== null
        ) {
            $block1->appendChild($this->hydrateAdditionalTxnFields($xml, $builder));
        }

        if ($builder->ecommerceInfo !== null) {
            $block1->appendChild($xml->createElement('Ecommerce', $builder->ecommerceInfo->channel));

            if (!empty($builder->invoiceNumber) || !empty($builder->ecommerceInfo->shipMonth)) {
                $direct = $xml->createElement('DirectMktData');
                if (!empty($builder->invoiceNumber)) {
                    $direct->appendChild($xml->createElement('DirectMktInvoiceNbr', $builder->invoiceNumber));
                }
                if (!empty($builder->ecommerceInfo->shipDay)) {
                    $direct->appendChild($xml->createElement('DirectMktShipDay', $builder->ecommerceInfo->shipDay));
                }
                if (!empty($builder->ecommerceInfo->shipMonth)) {
                    $direct->appendChild($xml->createElement('DirectMktShipMonth', $builder->ecommerceInfo->shipMonth));
                }
            }

            if (!empty($builder->ecommerceInfo->cavv)
                || !empty($builder->ecommerceInfo->eci)
                || !empty($builder->ecommerceInfo->xid)
            ) {
                $secure = $xml->createElement('SecureECommerce');
                if (!empty($builder->ecommerceInfo->paymentDataSource)) {
                    $direct->appendChild(
                        $xml->createElement(
                            'PaymentDataSource',
                            $builder->ecommerceInfo->paymentDataSource
                        )
                    );
                }
                if (!empty($builder->ecommerceInfo->paymentDataType)) {
                    $direct->appendChild(
                        $xml->createElement(
                            'TypeOfPaymentData',
                            $builder->ecommerceInfo->paymentDataType
                        )
                    );
                }
                if (!empty($builder->ecommerceInfo->cavv)) {
                    $direct->appendChild($xml->createElement('PaymentData', $builder->ecommerceInfo->cavv));
                }
                if (!empty($builder->ecommerceInfo->eci)) {
                    $direct->appendChild($xml->createElement('ECommerceIndicator', $builder->ecommerceInfo->eci));
                }
                if (!empty($builder->ecommerceInfo->xid)) {
                    $direct->appendChild($xml->createElement('XID', $builder->ecommerceInfo->xid));
                }
            }
        }

        if ($builder->dynamicDescriptor !== null) {
            $block1->appendChild(
                $xml->createElement('TxnDescriptor', $builder->dynamicDescriptor)
            );
        }

        $transaction->appendChild($block1);

        $response = $this->doTransaction($this->buildEnvelope($xml, $transaction));
        return $this->mapResponse($response, $builder, $this->buildEnvelope($xml, $transaction));
    }

    public function serializeRequest(AuthorizationBuilder $builder)
    {
        throw new UnsupportedTransactionException('Portico does not support hosted payments.');
    }

    /**
     * {@inheritdoc}
     *
     * @param ManagementBuilder $builder The transaction's builder
     *
     * @return Transaction
     */
    public function manageTransaction(ManagementBuilder $builder)
    {
        $xml = new DOMDocument('1.0', 'utf-8');

        // build request
        $transaction = $xml->createElement($this->mapRequestType($builder));

        if ($builder->transactionType !== TransactionType::BATCH_CLOSE) {
            $root = null;
            if ($builder->transactionType === TransactionType::REVERSAL
                || $builder->transactionType === TransactionType::REFUND
                || $builder->paymentMethod->paymentMethodType === PaymentMethodType::GIFT
                || $builder->paymentMethod->paymentMethodType === PaymentMethodType::ACH
            ) {
                $root = $xml->createElement('Block1');
            } else {
                $root = $transaction;
            }

            // Transaction ID
            if ($builder->paymentMethod !== null) {
                $root->appendChild(
                    $xml->createElement('GatewayTxnId', $builder->paymentMethod->transactionId)
                );
            }

            // Level II Data
            if ($builder->transactionType === TransactionType::EDIT
                && $builder->transactionModifier === TransactionModifier::LEVEL_II
            ) {
                $cpc = $xml->createElement('CPCData');

                if ($builder->poNumber !== null) {
                    $cpc->appendChild(
                        $xml->createElement('CardHolderPONbr', $builder->poNumber)
                    );
                }

                if ($builder->taxType !== null) {
                    $cpc->appendChild(
                        $xml->createElement(
                            'TaxType',
                            TaxType::validate($builder->taxType)
                        )
                    );
                }

                if ($builder->taxAmount !== null) {
                    $cpc->appendChild($xml->createElement('TaxAmt', $builder->taxAmount));
                }

                $root->appendChild($cpc);
            } else {
                // amount
                if ($builder->amount !== null) {
                    $root->appendChild($xml->createElement('Amt', $builder->amount));
                }

                // auth amount
                if ($builder->authAmount !== null) {
                    $root->appendChild($xml->createElement('AuthAmt', $builder->authAmount));
                }

                // gratuity
                if ($builder->gratuity !== null) {
                    $root->appendChild(
                        $xml->createElement('GratuityAmtInfo', $builder->gratuity)
                    );
                }
            }


            if ($builder->transactionType === TransactionType::REVERSAL
                || $builder->transactionType === TransactionType::REFUND
                || $builder->paymentMethod->paymentMethodType === PaymentMethodType::GIFT
                || $builder->paymentMethod->paymentMethodType === PaymentMethodType::ACH
            ) {
                $transaction->appendChild($root);
            }
        }

        $response = $this->doTransaction($this->buildEnvelope($xml, $transaction));
        return $this->mapResponse($response, $builder, $this->buildEnvelope($xml, $transaction));
    }

    public function processReport(ReportBuilder $builder)
    {
        $xml = new DOMDocument('1.0', 'utf-8');

        $transaction = $xml->createElement($this->mapReportType($builder->reportType));
        $transaction->appendChild($xml->createElement('TzConversion', $builder->timeZoneConversion));

        if ($builder instanceof TransactionReportBuilder) {
            if ($builder->deviceId !== null) {
                $transaction->appendChild($xml->createElement('DeviceId', $builder->deviceId));
            }

            if ($builder->startDate !== null) {
                $transaction->appendChild($xml->createElement('RptStartUtcDT', $builder->startDate->format()));
            }

            if ($builder->endDate !== null) {
                $transaction->appendChild($xml->createElement('RptEndUtcDT', $builder->endDate->format()));
            }

            if ($builder->transactionId !== null) {
                $transaction->appendChild($xml->createElement('TxnId', $builder->transactionId));
            }
        }

        $response = $this->doTransaction($this->buildEnvelope($xml, $transaction));
        return $this->mapReportResponse($response, $builder);
    }

    /**
     * Wraps a transaction with a SOAP envelope
     *
     * @param DOMDocument $xml The current DOMDocument object
     * @param DOMElement $transaction The current transaction to wrap
     *
     * @return DOMElement
     */
    protected function buildEnvelope(DOMDocument $xml, DOMElement $transaction)
    {
        $soapEnvelope = $xml->createElement('soapenv:Envelope');
        $soapEnvelope->setAttribute(
            'xmlns:soapenv',
            'http://schemas.xmlsoap.org/soap/envelope/'
        );
        $soapEnvelope->setAttribute('xmlns', static::XML_NAMESPACE);

        $soapBody = $xml->createElement('soapenv:Body');
        $request = $xml->createElement('PosRequest');

        $version = $xml->createElement('Ver1.0');
        $header = $xml->createElement('Header');

        if (!empty($this->secretApiKey)) {
            $header->appendChild(
                $xml->createElement('SecretAPIKey', trim($this->secretApiKey))
            );
        }
        if (!empty($this->siteId)) {
            $header->appendChild(
                $xml->createElement('SiteId', $this->siteId)
            );
        }
        if (!empty($this->deviceId)) {
            $header->appendChild(
                $xml->createElement('DeviceId', $this->deviceId)
            );
        }
        if (!empty($this->licenseId)) {
            $header->appendChild(
                $xml->createElement('LicenseId', $this->licenseId)
            );
        }
        if (!empty($this->username)) {
            $header->appendChild(
                $xml->createElement('UserName', $this->username)
            );
        }
        if (!empty($this->password)) {
            $header->appendChild(
                $xml->createElement('Password', $this->password)
            );
        }
        if (!empty($this->developerId)) {
            $header->appendChild(
                $xml->createElement('DeveloperID', $this->developerId)
            );
        }
        if (!empty($this->versionNumber)) {
            $header->appendChild(
                $xml->createElement('VersionNbr', $this->versionNumber)
            );
        }

        $version->appendChild($header);
        $transactionElement = $xml->createElement('Transaction');
        $transactionElement->appendChild($xml->importNode($transaction, true));
        $version->appendChild($transactionElement);

        $request->appendChild($version);
        $soapBody->appendChild($request);
        $soapEnvelope->appendChild($soapBody);
        $xml->appendChild($soapEnvelope);

        return $xml->saveXML();
    }

    /**
     * Deserializes the gateway's XML response
     *
     * @param string $rawResponse The XML response
     * @param BaseBuilder $builder The original transaction builder
     *
     * @throws Exception
     * @return Transaction
     */
    protected function mapResponse($rawResponse, BaseBuilder $builder, $request)
    {
        $result = new Transaction();

        // TODO: handle non-200 responses

        $root = $this->xml2object($rawResponse)->{'Ver1.0'};
        $acceptedCodes = ['00', '0', '85', '10'];

        $gatewayRspCode = $this->normalizeResponse((string)$root->Header->GatewayRspCode);
        $gatewayRspText = (string)$root->Header->GatewayRspMsg;

        if (!in_array($gatewayRspCode, $acceptedCodes)) {
            throw new GatewayException(
                sprintf(
                    'Unexpected Gateway Response: %s - %s. ' . $request . $rawResponse,
                    $gatewayRspCode,
                    $gatewayRspText
                )
            );
        }

        $item = $root->Transaction->{$this->mapRequestType($builder)};

        $result->responseCode = isset($item) && isset($item->RspCode)
            ? $this->normalizeResponse((string)$item->RspCode)
            : $gatewayRspCode;
        $result->responseMessage = isset($item) && isset($item->RspText)
            ? (string)$item->RspText
            : $gatewayRspText;

        if (isset($item) && isset($item->AuthAmt)) {
            $result->authorizedAmount = (string)$item->AuthAmt;
        }

        if (isset($item) && isset($item->AvailableBalance)) {
            $result->availableBalance = (string)$item->AvailableBalance;
        }

        if (isset($item) && isset($item->AVSRsltCode)) {
            $result->avsResponseCode = (string)$item->AVSRsltCode;
        }

        if (isset($item) && isset($item->AVSRsltText)) {
            $result->avsResponseMessage = (string)$item->AVSRsltText;
        }

        if (isset($item) && isset($item->BalanceAmt)) {
            $result->balanceAmount = (string)$item->BalanceAmt;
        }

        if (isset($item) && isset($item->CardType)) {
            $result->cardType = (string)$item->CardType;
        }

        if (isset($item) && isset($item->CardLast4)) {
            $result->cardLast4 = (string)$item->TokenPANLast4;
        }

        if (isset($item) && isset($item->CAVVResultCode)) {
            $result->cavvResponseCode = (string)$item->CAVVResultCode;
        }

        if (isset($item) && isset($item->CPCInd)) {
            $result->commercialIndicator = (string)$item->CPCInd;
        }

        if (isset($item) && isset($item->CVVRsltCode)) {
            $result->cvnResponseCode = (string)$item->CVVRsltCode;
        }

        if (isset($item) && isset($item->CVVRsltText)) {
            $result->cvnResponseMessage = (string)$item->CVVRsltText;
        }

        if (isset($item) && isset($item->EMVIssuerResp)) {
            $result->emvIssuerResponse = (string)$item->EMVIssuerResp;
        }

        if (isset($item) && isset($item->PointsBalanceAmt)) {
            $result->pointsBalanceAmount = (string)$item->PointsBalanceAmt;
        }

        if (isset($item) && isset($item->RecurringDataCode)) {
            $result->recurringDataCode = (string)$item->RecurringDataCode;
        }

        if (isset($item) && isset($item->RefNbr)) {
            $result->referenceNumber = (string)$item->RefNbr;
        }

        if (isset($item) && isset($item->TxnDescriptor)) {
            $result->transactionDescriptor = (string)$item->TxnDescriptor;
        }

        if ($builder->paymentMethod !== null) {
            $result->transactionReference = new TransactionReference();
            $result->transactionReference->transactionId = (string)$root->Header->GatewayTxnId;
            $result->transactionReference->paymentMethodType = $builder->paymentMethod->paymentMethodType;

            if (isset($item) && isset($item->AuthCode)) {
                $result->transactionReference->authCode = (string)$item->AuthCode;
            }
        }

        if (isset($item) && isset($item->CardData)) {
            $result->giftCard = new GiftCard();
            $result->giftCard->number = (string)$item->CardData->CardNbr;
            $result->giftCard->alias = (string)$item->CardData->Alias;
            $result->giftCard->pin = (string)$item->CardData->PIN;
        }

        if (isset($root->Header->TokenData) && isset($root->Header->TokenData->TokenValue)) {
            $result->token = (string)$root->Header->TokenData->TokenValue;
        }

        if (isset($item) && isset($item->BatchId)) {
            $result->batchSummary = new BatchSummary();
            $result->batchSummary->id = $item->BatchId;
            $result->batchSummary->transactionCount = $item->TxnCnt;
            $result->batchSummary->totalAmount = $item->TotalAmt;
            $result->batchSummary->sequenceNumber = $item->BatchSeqNbr;
        }

        return $result;
    }

    protected function mapReportResponse($rawResponse, ReportBuilder $builder)
    {
        $root = $this->xml2object($rawResponse)->{'Ver1.0'};
        $doc = $root->Transaction->{$this->mapReportType($builder)};

        if ($builder->reportType === ReportType::ACTIVITY && isset($doc->Details)) {
            $response = [];
            foreach ($doc->Details as $item) {
                $response[] = $this->hydrateTransactionSummary($item);
            }
            return $response;
        }

        if ($builder->reportType === ReportType::TRANSACTION_DETAIL) {
            return $this->hydrateTransactionSummary($doc);
        }

        return null;
    }

    protected function hydrateTransactionSummary($item)
    {
        $summary = new TransactionSummary();

        if (isset($item) && isset($item->Amt)) {
            $summary->amount = $item->Amt;
        }

        if (isset($item) && isset($item->AuthAmt)) {
            $summary->authorizatedAmount = $item->AuthAmt;
        }

        if (isset($item) && isset($item->AuthCode)) {
            $summary->authCode = $item->AuthCode;
        }

        if (isset($item) && isset($item->ClientTxnId)) {
            $summary->clientTransactionId = $item->ClientTxnId;
        }

        if (isset($item) && isset($item->DeviceId)) {
            $summary->deviceId = $item->DeviceId;
        }

        if (isset($item) && (isset($item->RspCode) || isset($item->IssuerRspCode))) {
            $summary->issuerResponseCode = isset($item->RspCode) ? $item->RspCode : $item->IsserRspCode;
        }

        if (isset($item) && (isset($item->RspText) || isset($item->IssuerRspText))) {
            $summary->issuerResponseMessage = isset($item->RspText) ? $item->RspText : $item->IsserRspText;
        }

        if (isset($item) && isset($item->MaskedCardNbr)) {
            $summary->maskedCardNumber = $item->MaskedCardNbr;
        }

        if (isset($item) && isset($item->OriginalGatewayTxnId)) {
            $summary->originalTransactionId = $item->OriginalGatewayTxnId;
        }

        if (isset($item) && isset($item->GatewayRspCode)) {
            $summary->gatewayResponseCode = $this->normalizeResponse($item->GatewayRspCode);
        }

        if (isset($item) && isset($item->GatewayResponseMsg)) {
            $summary->gatewayResponseMessage = $item->GatewayResponseMsg;
        }

        if (isset($item) && isset($item->RefNbr)) {
            $summary->referenceNumber = $item->RefNbr;
        }

        if (isset($item) && isset($item->ServiceName)) {
            $summary->serviceName = $item->ServiceName;
        }

        if (isset($item) && isset($item->SettlementAmt)) {
            $summary->settlementAmount = $item->SettlementAmt;
        }

        if (isset($item) && (isset($item->TxnStatus) || isset($item->Status))) {
            $summary->status = isset($item->TxnStatus) ? $item->TxnStatus : $item->Status;
        }

        if (isset($item) && (isset($item->TxnUtcDT) || isset($item->ReqUtcDT))) {
            $summary->transactionDate = isset($item->TxnUtcDT) ? $item->TxnUtcDT : $item->ReqUtcDT;
        }

        if (isset($item) && isset($item->GatewayTxnId)) {
            $summary->transactionId = $item->GatewayTxnId;
        }

        if (isset($item) && isset($item->ConvenienceAmtInfo)) {
            $summary->convenienceAmount = $item->ConvenienceAmtInfo;
        }

        if (isset($item) && isset($item->ShippingAmtInfo)) {
            $summary->shippingAmount = $item->ShippingAmtInfo;
        }

        return $summary;
    }

    /**
     * Maps a transaction builder to a Portico request type
     *
     * @param BaseBuilder $builder Transaction builder
     *
     * @return string
     */
    protected function mapRequestType(BaseBuilder $builder)
    {
        switch ($builder->transactionType) {
            case TransactionType::BATCH_CLOSE:
                return 'BatchClose';
            case TransactionType::DECLINE:
                if ($builder->paymentMethod->paymentMethodType === PaymentMethodType::GIFT) {
                    return 'GiftCardDeactivate';
                } elseif ($builder->transactionModifier === TransactionModifier::CHIP_DECLINE) {
                    return 'ChipCardDecline';
                } elseif ($builder->transactionModifier === TransactionModifier::FRAUD_DECLINE) {
                    return 'OverrideFraudDecline';
                }
                throw new NotImplementedException();
            case TransactionType::VERIFY:
                if ($builder->transactionModifier === TransactionModifier::ENCRYPTED_MOBILE) {
                    throw new UnsupportedTransactionException('Transaction not supported for this payment method.');
                }
                return 'CreditAccountVerify';
            case TransactionType::CAPTURE:
                return 'CreditAddToBatch';
            case TransactionType::AUTH:
                if ($builder->paymentMethod->paymentMethodType === PaymentMethodType::CREDIT) {
                    if ($builder->transactionModifier === TransactionModifier::ADDITIONAL) {
                        return 'CreditAdditionalAuth';
                    } elseif ($builder->transactionModifier === TransactionModifier::INCREMENTAL) {
                        return 'CreditIncrementalAuth';
                    } elseif ($builder->transactionModifier === TransactionModifier::OFFLINE) {
                        return 'CreditOfflineAuth';
                    } elseif ($builder->transactionModifier === TransactionModifier::ENCRYPTED_MOBILE) {
                        throw new UnsupportedTransactionException('Transaction not supported for this payment method.');
                    }

                    return 'CreditAuth';
                } elseif ($builder->paymentMethod->paymentMethodType === PaymentMethodType::RECURRING) {
                    return 'RecurringBillingAuth';
                }
                throw new UnsupportedTransactionException('Transaction not supported for this payment method.');
            case TransactionType::SALE:
                if ($builder->paymentMethod->paymentMethodType === PaymentMethodType::CREDIT) {
                    if ($builder->transactionModifier === TransactionModifier::OFFLINE) {
                        return 'CreditOfflineSale';
                    } elseif ($builder->transactionModifier === TransactionModifier::ENCRYPTED_MOBILE) {
                        throw new UnsupportedTransactionException('Transaction not supported for this payment method.');
                    } else {
                        return 'CreditSale';
                    }
                } elseif ($builder->paymentMethod->paymentMethodType === PaymentMethodType::DEBIT) {
                    return 'DebitSale';
                } elseif ($builder->paymentMethod->paymentMethodType === PaymentMethodType::CASH) {
                    return 'CashSale';
                } elseif ($builder->paymentMethod->paymentMethodType === PaymentMethodType::ACH) {
                    return 'CheckSale';
                } elseif ($builder->paymentMethod->paymentMethodType === PaymentMethodType::EBT) {
                    if ($builder->transactionModifier === TransactionModifier::CASH_BACK) {
                        return 'EBTCashBackPurchase';
                    } elseif ($builder->transactionModifier === TransactionModifier::VOUCHER) {
                        return 'EBTVoucherPurchase';
                    } else {
                        return 'EBTFSPurchase';
                    }
                } elseif ($builder->paymentMethod->paymentMethodType === PaymentMethodType::GIFT) {
                    return 'GiftCardSale';
                }
                throw new UnsupportedTransactionException('Transaction not supported for this payment method.');
            case TransactionType::REFUND:
                if ($builder->paymentMethod->paymentMethodType === PaymentMethodType::CREDIT) {
                    return 'CreditReturn';
                } elseif ($builder->paymentMethod->paymentMethodType === PaymentMethodType::DEBIT) {
                    return 'DebitReturn';
                } elseif ($builder->paymentMethod->paymentMethodType === PaymentMethodType::CASH) {
                    return 'CashReturn';
                } elseif ($builder->paymentMethod->paymentMethodType === PaymentMethodType::EBT) {
                    return 'EBTFSReturn';
                }
                throw new UnsupportedTransactionException('Transaction not supported for this payment method.');
            case TransactionType::REVERSAL:
                if ($builder->paymentMethod->paymentMethodType === PaymentMethodType::CREDIT) {
                    return 'CreditReversal';
                } elseif ($builder->paymentMethod->paymentMethodType === PaymentMethodType::DEBIT) {
                    return 'DebitReversal';
                } elseif ($builder->paymentMethod->paymentMethodType === PaymentMethodType::GIFT) {
                    return 'GiftCardReversal';
                }
                throw new UnsupportedTransactionException('Transaction not supported for this payment method.');
            case TransactionType::EDIT:
                if ($builder->transactionModifier === TransactionModifier::LEVEL_II) {
                    return 'CreditCPCEdit';
                } else {
                    return 'CreditTxnEdit';
                }
                break;
            case TransactionType::VOID:
                if ($builder->paymentMethod->paymentMethodType === PaymentMethodType::CREDIT) {
                    return 'CreditVoid';
                } elseif ($builder->paymentMethod->paymentMethodType === PaymentMethodType::ACH) {
                    return 'CheckVoid';
                } elseif ($builder->paymentMethod->paymentMethodType === PaymentMethodType::GIFT) {
                    return 'GiftCardVoid';
                }
                throw new UnsupportedTransactionException('Transaction not supported for this payment method.');
            case TransactionType::ADD_VALUE:
                if ($builder->paymentMethod->paymentMethodType === PaymentMethodType::CREDIT) {
                    return 'PrePaidAddValue';
                } elseif ($builder->paymentMethod->paymentMethodType === PaymentMethodType::DEBIT) {
                    return 'DebitAddValue';
                } elseif ($builder->paymentMethod->paymentMethodType === PaymentMethodType::GIFT) {
                    return 'GiftCardAddValue';
                }
                throw new UnsupportedTransactionException('Transaction not supported for this payment method.');
            case TransactionType::BALANCE:
                if ($builder->paymentMethod->paymentMethodType === PaymentMethodType::CREDIT) {
                    return 'PrePaidBalanceInquiry';
                } elseif ($builder->paymentMethod->paymentMethodType === PaymentMethodType::EBT) {
                    return 'EBTBalanceInquiry';
                } elseif ($builder->paymentMethod->paymentMethodType === PaymentMethodType::GIFT) {
                    return 'GiftCardBalance';
                }
                throw new UnsupportedTransactionException('Transaction not supported for this payment method.');
            case TransactionType::ACTIVATE:
                return 'GiftCardActivate';
            case TransactionType::ALIAS:
                return 'GiftCardAlias';
            case TransactionType::REPLACE:
                return 'GiftCardReplace';
            case TransactionType::REWARD:
                return 'GiftCardReward';
            default:
                break;
        }

        throw new UnsupportedTransactionException('Unknown transaction');
    }

    protected function mapReportType(ReportBuilder $builder)
    {
        switch ($builder->reportType) {
            case ReportType::ACTIVITY:
                return 'ReportActivity';
            case ReportType::TRANSACTION_DETAIL:
                return 'ReportTxnDetail';
            default:
                throw new UnsupportedTransactionException();
        }
    }

    /**
     * Converts a XML string to a simple object for use,
     * removing extra nodes that are not necessary for
     * handling the response
     *
     * @param string $xml Response XML from the gateway
     *
     * @return \SimpleXMLElement
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
            $children = $response->children(static::XML_NAMESPACE);
            foreach ($children as $item) {
                return $item;
            }
        }

        throw new Exception('XML from gateway could not be parsed');
    }

    /**
     * Tests the payment method for a token value
     *
     * @param IPaymentMethod $paymentMethod The payment method
     *
     * @return [bool, string|null]
     */
    protected function hasToken(IPaymentMethod $paymentMethod)
    {
        $tokenValue = null;

        if ($paymentMethod instanceof ITokenizable && !empty($paymentMethod->token)) {
            $tokenValue = $paymentMethod->token;
            return [true, $tokenValue];
        }

        return [false, $tokenValue];
    }

    /**
     * Normalizes response code for success responses
     *
     * @param string $input Original response code
     *
     * @return string
     */
    protected function normalizeResponse($input)
    {
        if (in_array($input, ['0', '85'])) {
            $input = '00';
        }

        return $input;
    }

    /**
     * Serializes builder information into XML
     *
     * @param DOMDocument $xml XML instance
     * @param BaseBuilder $builder Request builder
     * @param bool $isCheck If payment method is ACH
     *
     * @return DOMElement
     */
    protected function hydrateHolder(DOMDocument $xml, BaseBuilder $builder, $isCheck = false)
    {
        $holder = $xml->createElement($isCheck ? 'ConsumerInfo' : 'CardHolderData');
        $holder->appendChild(
            $xml->createElement($isCheck ? 'Address1' : 'CardHolderAddr', $builder->billingAddress->streetAddress1)
        );
        $holder->appendChild(
            $xml->createElement($isCheck ? 'City' : 'CardHolderCity', $builder->billingAddress->city)
        );
        $holder->appendChild(
            $xml->createElement($isCheck ? 'State' : 'CardHolderState', $builder->billingAddress->getProvince())
        );
        $holder->appendChild(
            $xml->createElement($isCheck ? 'Zip' : 'CardHolderZip', $builder->billingAddress->postalCode)
        );

        if ($isCheck) {
            if (!empty($builder->paymentMethod->checkHolderName)) {
                $names = explode(' ', $builder->paymentMethod->checkHolderName, 2);
                $holder->appendChild(
                    $xml->createElement('FirstName', $names[0])
                );

                if (isset($names[1])) {
                    $holder->appendChild(
                        $xml->createElement('LastName', $names[1])
                    );
                }
            }

            if ($builder->paymentMethod->checkHolderName !== null) {
                $holder->appendChild($xml->createElement('CheckName', $builder->paymentMethod->checkHolderName));
            }

            if ($builder->paymentMethod->phoneNumber !== null) {
                $holder->appendChild($xml->createElement('PhoneNumber', $builder->paymentMethod->phoneNumber));
            }

            if ($builder->paymentMethod->driversLicenseNumber !== null) {
                $holder->appendChild($xml->createElement('DLNumber', $builder->paymentMethod->driversLicenseNumber));
            }

            if ($builder->paymentMethod->driversLicenseState !== null) {
                $holder->appendChild($xml->createElement('DLState', $builder->paymentMethod->driversLicenseState));
            }

            if ($builder->paymentMethod->ssnLast4 !== null
                || $builder->paymentMethod->birthYear !== null
            ) {
                $identity = $xml->createElement('IdentityInfo');
                $identity->appendChild($xml->createElement('SSNL4', $builder->paymentMethod->ssnLast4));
                $identity->appendChild($xml->createElement('DOBYear', $builder->paymentMethod->birthYear));
                $holder->appendChild($identity);
            }
        }

        return $holder;
    }

    protected function hydrateAccountType($type)
    {
        switch ($type) {
            case AccountType::CHECKING:
                return 'CHECKING';
            case AccountType::SAVINGS:
                return 'SAVINGS';
        }
    }

    /**
     * Serializes builder information into XML
     *
     * @param DOMDocument $xml XML instance
     * @param BaseBuilder $builder Request builder
     *
     * @return DOMElement
     */
    protected function hydrateAdditionalTxnFields(DOMDocument $xml, BaseBuilder $builder)
    {
        $additionalTxnFields = $xml->createElement('AdditionalTxnFields');

        if ($builder->description !== null && $builder->description !== '') {
            $additionalTxnFields->appendChild(
                $xml->createElement('Description', $builder->description)
            );
        }

        if ($builder->invoiceNumber !== null && $builder->invoiceNumber !== '') {
            $additionalTxnFields->appendChild(
                $xml->createElement('InvoiceNbr', $builder->invoiceNumber)
            );
        }

        if ($builder->customerId !== null && $builder->customerId !== '') {
            $additionalTxnFields->appendChild(
                $xml->createElement('CustomerID', $builder->customerId)
            );
        }

        return $additionalTxnFields;
    }

    protected function hydrateCheckType($type)
    {
        switch ($type) {
            case CheckType::PERSONAL:
                return 'PERSONAL';
            case CheckType::BUSINESS:
                return 'BUSINESS';
            case CheckType::PAYROLL:
                return 'PAYROLL';
        }
    }

    /**
     * Serializes builder information into XML
     *
     * @param DOMDocument $xml XML instance
     * @param BaseBuilder $builder Request builder
     *
     * @return DOMElement
     */
    protected function hydrateEncryptionData(DOMDocument $xml, BaseBuilder $builder)
    {
        $enc = $xml->createElement('EncryptionData');

        if ($builder->paymentMethod->encryptionData->version !== null) {
            $enc->appendChild($xml->createElement('Version', $builder->paymentMethod->encryptionData->version));
        }

        if ($builder->paymentMethod->encryptionData->trackNumber !== null) {
            $enc->appendChild($xml->createElement('TrackNumber', $builder->paymentMethod->encryptionData->trackNumber));
        }

        if ($builder->paymentMethod->encryptionData->ktb !== null) {
            $enc->appendChild($xml->createElement('KTB', $builder->paymentMethod->encryptionData->ktb));
        }

        if ($builder->paymentMethod->encryptionData->ksn !== null) {
            $enc->appendChild($xml->createElement('KSN', $builder->paymentMethod->encryptionData->ksn));
        }

        return $enc;
    }

    protected function hydrateEntryMethod($method)
    {
        switch ($method) {
            case EntryMethod::MANUAL:
                return 'Manual';
            case EntryMethod::SWIPE:
                return 'Swipe';
            case EntryMethod::PROXIMITY:
                return 'Proximity';
        }
    }

    /**
     * Serializes builder information into XML
     *
     * @param DOMDocument $xml XML instance
     * @param BaseBuilder $builder Request builder
     * @param bool $hasToken If request builder is using token data
     * @param string $tokenValue Token if `$hasToken` is `true`
     *
     * @return DOMElement
     */
    protected function hydrateManualEntry(DOMDocument $xml, BaseBuilder $builder, $hasToken = false, $tokenValue = null)
    {
        $me = $xml->createElement('ManualEntry');

        if (isset($builder->paymentMethod->number)) {
            $me->appendChild(
                $xml->createElement(
                    $hasToken ? 'TokenValue' : 'CardNbr',
                    $hasToken ? $tokenValue : $builder->paymentMethod->number
                )
            );
        }

        if (isset($builder->paymentMethod->expMonth)) {
            $me->appendChild($xml->createElement('ExpMonth', $builder->paymentMethod->expMonth));
        }

        if (isset($builder->paymentMethod->expYear)) {
            $me->appendChild($xml->createElement('ExpYear', $builder->paymentMethod->expYear));
        }

        if (isset($builder->paymentMethod->cvn)) {
            $me->appendChild($xml->createElement('CVV2', $builder->paymentMethod->cvn));
        }

        $me->appendChild(
            $xml->createElement('CardPresent', ($builder->paymentMethod->cardPresent ? 'Y' : 'N'))
        );

        $me->appendChild(
            $xml->createElement('ReaderPresent', ($builder->paymentMethod->readerPresent ? 'Y' : 'N'))
        );

        return $me;
    }

    /**
     * Serializes builder information into XML
     *
     * @param DOMDocument $xml XML instance
     * @param BaseBuilder $builder Request builder
     * @param bool $hasToken If request builder is using token data
     * @param string $tokenValue Token if `$hasToken` is `true`
     *
     * @return DOMElement
     */
    protected function hydrateTrackData(DOMDocument $xml, BaseBuilder $builder, $hasToken = false, $tokenValue = null)
    {
        $trackData = $xml->createElement($hasToken ? 'TokenValue' : 'TrackData');

        if ($hasToken) {
            $trackData->appendChild($xml->createElement('TokenValue', $tokenValue));
            return $trackData;
        }

        $trackData->appendChild($xml->createTextNode($builder->paymentMethod->value));
        if ($builder->paymentMethod->paymentMethodType !== PaymentMethodType::DEBIT) {
            $trackData->setAttribute(
                'method',
                $builder->paymentMethod->entryMethod === EntryMethod::SWIPE
                    ? 'swipe'
                    : 'proximity'
            );
        }

        return $trackData;
    }
}
