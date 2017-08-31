<?php

namespace GlobalPayments\Api\Gateways;

use DOMDocument;
use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Builders\ManagementBuilder;
use GlobalPayments\Api\Builders\RecurringBuilder;
use GlobalPayments\Api\Builders\ReportBuilder;
use GlobalPayments\Api\Entities\Enums\CvnPresenceIndicator;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\TransactionReference;
use GlobalPayments\Api\Utils\GenerationUtils;

class RealexConnector extends XmlGateway implements IPaymentGateway, IRecurringService
{
    /**
     * Merchant ID to authenticate with the gateway
     *
     * @var string
     */
    public $merchantId;

    /**
     * Account ID to authenticate with the gateway
     *
     * @var string
     */
    public $accountId;

    /**
     * Shared secret to authenticate with the gateway
     *
     * @var string
     */
    public $sharedSecret;

    /**
     * Channel to describe the transactions
     *
     * @var string
     */
    public $channel;

    /**
     * {@inheritdoc}
     *
     * @param AuthorizationBuilder $builder The transaction's builder
     *
     * @return Transaction
     */
    public function processAuthorization(AuthorizationBuilder $builder)
    {
        $xml = new DOMDocument();
        $timestamp = GenerationUtils::generateTimestamp();
        $orderId = isset($builder->orderId)
            ? $builder->orderId
            : GenerationUtils::generateOrderId();

        $request = $xml->createElement('request');
        $request->setAttribute('timestamp', $timestamp);
        $request->setAttribute(
            'type',
            $this->mapAuthRequestType($builder)
        );

        $request->appendChild($xml->createElement('merchantid', $this->merchantId));
        $request->appendChild($xml->createElement('account', $this->accountId));
        $request->appendChild($xml->createElement('channel', $this->channel));
        $request->appendChild($xml->createElement('orderid', $orderId));

        if (!empty($builder->amount)) {
            $amount = $xml->createElement('amount', $builder->amount);
            $amount->setAttribute('currency', $builder->currency);
            $request->appendChild($amount);
        }

        $paymentHash = null;
        if ($builder->paymentMethod instanceof CreditCardData) {
            $card = $builder->paymentMethod;
            $paymentHash = $card->number;

            $cardElement = $xml->createElement('card');
            $cardElement->appendChild($xml->createElement('number', $card->number));
            $cardElement->appendChild(
                $xml->createElement(
                    'expdate',
                    $card->expMonth . substr($card->expYear, 2, 2)
                )
            );
            $cardElement->appendChild(
                $xml->createElement(
                    'type',
                    strtoupper($card->getCardType())
                )
            );
            $cardElement->appendChild(
                $xml->createElement(
                    'chname',
                    $card->cardHolderName
                )
            );

            if (isset($card->cvn)) {
                $cvn = $xml->createElement('cvn');
                $cvn->appendChild(
                    $xml->createElement(
                        'number',
                        $card->cvn
                    )
                );
                $cvn->appendChild(
                    $xml->createElement(
                        'presind',
                        CvnPresenceIndicator::validate($card->cvnPresenceIndicator)
                    )
                );
                $cardElement->appendChild($cvn);
            }

            $request->appendChild($cardElement);

            $isVerify = $builder->transactionType ===
                TransactionType::VERIFY;
            $request->appendChild(
                $xml->createElement(
                    'sha1hash',
                    $this->generateHash(
                        $timestamp,
                        $orderId,
                        $builder->amount,
                        $builder->currency,
                        $card->number,
                        $isVerify
                    )
                )
            );
        }

        // refund hash
        if ($builder->transactionType == TransactionType::REFUND) {
            $request->appendChild(
                $xml->createElement(
                    'refundhash',
                    'e51118f58785274e117efe1bf99d4d50ccb96949'
                )
            );
        }

        // this needs to be figured out based on txn type and set to 0, 1, or MULTI
        if ($builder->transactionType === TransactionType::SALE
            || $builder->transactionType === TransactionType::AUTH
        ) {
            $autoSettle = $builder->transactionType === TransactionType::SALE
                ? '1' : '0';
            $autoSettleElement = $xml->createElement('autosettle');
            $autoSettleElement->setAttribute('flag', $autoSettle);
            $request->appendChild($autoSettleElement);
        }

        $xml->appendChild($request);
        $response = $this->doTransaction($xml->saveXML());
        return $this->mapResponse($response);
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
        $xml = new DOMDocument();
        $timestamp = GenerationUtils::generateTimestamp();
        $orderId = GenerationUtils::generateOrderId();

        $request = $xml->createElement('request');
        $request->setAttribute('timestamp', $timestamp);
        $request->setAttribute(
            'type',
            $this->mapManageRequestType($builder)
        );

        $request->appendChild($xml->createElement('merchantid', $this->merchantId));
        $request->appendChild($xml->createElement('account', $this->accountId));
        $request->appendChild($xml->createElement('channel', $this->channel));
        $request->appendChild($xml->createElement('orderid', $orderId));
        $request->appendChild(
            $xml->createElement(
                'pasref',
                $builder->paymentMethod->transactionId
            )
        );

        if (!empty($builder->amount)) {
            $amount = $xml->createElement('amount', $builder->amount);
            $request->appendChild($amount);
        }
        // $amount->setAttribute('currency', $builder->currency);

        $request->appendChild(
            $xml->createElement(
                'sha1hash',
                $this->generateHash(
                    $timestamp,
                    $orderId,
                    $builder->amount,
                    $builder->currency,
                    ''
                )
            )
        );
        $xml->appendChild($request);

        $response = $this->doTransaction($xml->saveXML());
        return $this->mapResponse($response);
    }

    public function processReport(ReportBuilder $builder)
    {
        throw new UnsupportedTransactionException();
    }

    public function serializeRequest(AuthorizationBuilder $builder)
    {
        throw new UnsupportedTransactionException();
    }

    public function processRecurring(RecurringBuilder $builder)
    {
        throw new UnsupportedTransactionException();
    }

    /**
     * Deserializes the gateway's XML response
     *
     * @param string $rawResponse The XML response
     *
     * @return Transaction
     */
    protected function mapResponse($rawResponse)
    {
        $result = new Transaction();

        $root = $this->xml2object($rawResponse);

        $result->responseCode = (string)$root->result;
        $result->responseMessage = (string)$root->message;
        $result->transactionReference = new TransactionReference();
        $result->transactionReference->paymentMethodType = PaymentMethodType::CREDIT;
        $result->transactionReference->transactionId = (string)$root->pasref;

        return $result;
    }

    /**
     * Generates a request hash from the request data
     *
     * @param string $timestamp Request timestamp
     * @param string $orderId Request order ID
     * @param string $amount Request amount
     * @param string $currency Request currency
     * @param string $paymentData Request payment data
     * @param bool $verify Is request a verify transaction
     *
     * @return string
     */
    protected function generateHash(
        $timestamp,
        $orderId,
        $amount,
        $currency,
        $paymentData = null,
        $verify = false
    ) {
        $data = [
            $timestamp,
            $this->merchantId,
            $orderId,
        ];

        if (false === $verify) {
            $data[] = $amount;
            $data[] = $currency;
        }

        $data[] = $paymentData;

        return GenerationUtils::generateHash(
            implode('.', $data),
            $this->sharedSecret
        );
    }

    /**
     * Maps a transaction builder to a Realex request type
     *
     * @param AuthorizationBuilder $builder Transaction builder
     *
     * @return string
     */
    protected function mapAuthRequestType(AuthorizationBuilder $builder)
    {
        switch ($builder->transactionType) {
            case TransactionType::SALE:
            case TransactionType::AUTH:
                if ($builder->transactionModifier === TransactionModifier::OFFLINE) {
                    return 'offline';
                }
                return 'auth';
            case TransactionType::CAPTURE:
                return 'settle';
            case TransactionType::VERIFY:
                return 'otb';
            case TransactionType::REFUND:
                return 'credit';
            case TransactionType::REVERSAL:
                // TODO: should be customer type
                throw new UnsupportedTransactionException(
                    'The selected gateway does not support this transaction type.'
                );
            default:
                return 'unknown';
        }
    }

    /**
     * Maps a transaction builder to a Realex request type
     *
     * @param ManagementBuilder $builder Transaction builder
     *
     * @return string
     */
    protected function mapManageRequestType(ManagementBuilder $builder)
    {
        switch ($builder->transactionType) {
            case TransactionType::CAPTURE:
                return 'settle';
            case TransactionType::REFUND:
                return 'rebate';
            case TransactionType::VOID:
            case TransactionType::REVERSAL:
                return 'void';
            default:
                return 'unknown';
        }
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
}
