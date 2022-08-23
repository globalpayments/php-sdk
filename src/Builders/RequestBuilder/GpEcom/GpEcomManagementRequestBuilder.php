<?php

namespace GlobalPayments\Api\Builders\RequestBuilder\GpEcom;

use DOMDocument;
use GlobalPayments\Api\Builders\BaseBuilder;
use GlobalPayments\Api\Builders\ManagementBuilder;
use GlobalPayments\Api\Entities\Enums\AlternativePaymentType;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Exceptions\BuilderException;
use GlobalPayments\Api\Entities\Request;
use GlobalPayments\Api\Mapping\GpEcomMapping;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpEcomConfig;
use GlobalPayments\Api\Utils\GenerationUtils;

class GpEcomManagementRequestBuilder extends GpEcomRequestBuilder
{
    /**
     * @param $builder
     * @return bool
     */
    public static function canProcess($builder)
    {
        if ($builder instanceof ManagementBuilder) {
            return true;
        }

        return false;
    }

    /**
     * @param BaseBuilder $builder
     * @param GpEcomConfig $config
     *
     * @return Request
     */
    public function buildRequest(BaseBuilder $builder, GpEcomConfig $config)
    {
        /** @var ManagementBuilder $builder */
        $xml = new DOMDocument();
        $timestamp = GenerationUtils::generateTimestamp();
        $orderId = $builder->orderId ?: GenerationUtils::generateOrderId();
        $transactionType = GpEcomMapping::mapManageRequestType($builder);
        // Build Request
        $request = $xml->createElement("request");
        $request->setAttribute("timestamp", $timestamp);
        $request->setAttribute("type", $transactionType);

        $request->appendChild($xml->createElement("merchantid", $config->merchantId ?? ''));

        if ($config->accountId !== null) {
            $request->appendChild($xml->createElement("account", $config->accountId ?? ''));
        }
        if (is_null($builder->alternativePaymentType)) {
            $request->appendChild($xml->createElement("channel", $config->channel ?? ''));
        }

        if ($builder->amount !== null) {
            $amount = $xml->createElement("amount", preg_replace('/[^0-9]/', '', sprintf('%01.2f', $builder->amount)));
            $amount->setAttribute("currency", $builder->currency ?? '');
            $request->appendChild($amount);
        } elseif ($builder->transactionType === TransactionType::CAPTURE) {
            throw new BuilderException("Amount cannot be null for capture.");
        }

        $request->appendChild($xml->createElement("orderid", $orderId));
        $request->appendChild($xml->createElement("pasref", $builder->transactionId ?? ''));

        // rebate hash
        if ($builder->transactionType === TransactionType::REFUND &&
            is_null($builder->alternativePaymentType)) {
            $request->appendChild($xml->createElement("authcode", $builder->paymentMethod->authCode ?? ''));
        }

        // reason code
        if ($builder->reasonCode !== null) {
            $request->appendChild($xml->createElement("reasoncode", $builder->reasonCode ?? ''));
        }

        if ($builder->alternativePaymentType !== null) {
            $request->appendChild($xml->createElement("paymentmethod", $builder->alternativePaymentType ?? ''));
            if ($builder->transactionType == TransactionType::CONFIRM) {
                $paymentMethodDetails = $xml->createElement("paymentmethoddetails");
                $apmResponse = $builder->paymentMethod->alternativePaymentResponse;
                if ($builder->alternativePaymentType == AlternativePaymentType::PAYPAL && isset($apmResponse)) {
                    $paymentMethodDetails->appendChild($xml->createElement('Token', $apmResponse->sessionToken ?? ''));
                    $paymentMethodDetails->appendChild(
                        $xml->createElement('PayerID', $apmResponse->providerReference ?? '')
                    );
                }
                $request->appendChild($paymentMethodDetails);
            }
        }

        if ($builder->transactionType === TransactionType::VERIFY_SIGNATURE) {
            $request->appendChild($xml->createElement("pares", $builder->payerAuthenticationResponse ?? ''));
        }

        //supplementarydata
        if (
            in_array($builder->transactionType,[TransactionType::REFUND, TransactionType::CAPTURE]) &&
            !empty($builder->supplementaryData)
        ) {
            $this->buildSupplementaryData($builder, $xml,$request);
        }

        // comments needs to be multiple
        if ($builder->description !== null) {
            $comments = $xml->createElement("comments");
            $comment = $xml->createElement("comment", $builder->description ?? '');
            $comment->setAttribute("id", "1");
            $comments->appendChild($comment);
            $request->appendChild($comments);
        }

        $toHash = [
            $timestamp,
            $config->merchantId,
            $orderId,
            ($builder->amount !== null ? preg_replace('/[^0-9]/', '', sprintf('%01.2f', $builder->amount)) : ''),
            ($builder->currency !== null ? $builder->currency : ''),
            ($builder->alternativePaymentType !== null ? $builder->alternativePaymentType : '')
        ];

        if (
            (
                $builder->transactionType === TransactionType::CAPTURE ||
                $builder->transactionType === TransactionType::REFUND
            ) &&
            !empty($builder->dynamicDescriptor)
        ) {
            $narrative = $xml->createElement("narrative");
            $narrative->appendChild($xml->createElement("chargedescription", strtoupper($builder->dynamicDescriptor)));
            $request->appendChild($narrative);
        }

        $request->appendChild(
            $xml->createElement(
                "sha1hash",
                GenerationUtils::generateHash($config->sharedSecret, implode('.', $toHash))
            )
        );

        // rebate hash
        if ($builder->transactionType === TransactionType::REFUND) {
            $request->appendChild(
                $xml->createElement(
                    "refundhash",
                    GenerationUtils::generateHash(isset($config->rebatePassword) ? $config->rebatePassword : '')
                )
            );
        }

        return new Request('', 'POST', $xml->saveXML($request));
    }
}