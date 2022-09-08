<?php

namespace GlobalPayments\Api\Builders\RequestBuilder\GpEcom;

use GlobalPayments\Api\Builders\BaseBuilder;
use GlobalPayments\Api\Builders\RecurringBuilder;
use GlobalPayments\Api\Entities\Customer;
use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException;
use GlobalPayments\Api\Entities\IRequestBuilder;
use GlobalPayments\Api\Entities\Reporting\SearchCriteria;
use GlobalPayments\Api\Entities\Request;
use GlobalPayments\Api\Entities\Schedule;
use GlobalPayments\Api\Mapping\EnumMapping;
use GlobalPayments\Api\PaymentMethods\RecurringPaymentMethod;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpEcomConfig;
use GlobalPayments\Api\Utils\GenerationUtils;
use DOMDocument;

class GpEcomRecurringRequestBuilder implements IRequestBuilder
{
    /***
     * @param RecurringBuilder $builder
     *
     * @return bool
     */
    public static function canProcess($builder)
    {
        if ($builder instanceof RecurringBuilder) {
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
    public function buildRequest(BaseBuilder $builder, $config)
    {
        /** @var RecurringBuilder $builder */
        $xml = new DOMDocument();
        $timestamp = GenerationUtils::generateTimestamp();
        $orderId = $builder->orderId ? $builder->orderId : GenerationUtils::generateOrderId();
        $shaHashTagName = "sha1hash";

        // Build Request
        $request = $xml->createElement("request");
        $request->setAttribute("timestamp", $timestamp);
        $request->setAttribute("type", $this->mapRecurringRequestType($builder));

        $request->appendChild($xml->createElement("merchantid", $config->merchantId));


        /**
         * @var RecurringBuilder $builder
         */
        switch ($builder->transactionType) {
            case TransactionType::CREATE:
            case TransactionType::EDIT:
                $request->appendChild($xml->createElement("channel", $config->channel ?? ''));
                if ($config->accountId !== null) {
                    $request->appendChild($xml->createElement("account", $config->accountId));
                }
                if ($builder->entity instanceof Customer) {
                    $request->appendChild($xml->createElement("orderid", $orderId));
                    $hash = GenerationUtils::generateHash(
                        $config->sharedSecret,
                        implode('.', [
                            $timestamp,
                            $config->merchantId,
                            $orderId,
                            '',
                            '',
                            $builder->entity->key
                        ])
                    );

                    $request->appendChild($this->buildCustomer($xml, $builder));
                } elseif ($builder->entity instanceof RecurringPaymentMethod) {
                    $payment = $builder->entity;
                    $paymentKey = (!empty($payment->key)) ? $payment->key : $payment->id;

                    if ($builder->transactionType == TransactionType::CREATE) {
                        $hash = GenerationUtils::generateHash(
                            $config->sharedSecret,
                            implode('.', [
                                $timestamp,
                                $config->merchantId,
                                $orderId,
                                '',
                                '',
                                $payment->customerKey,
                                $payment->paymentMethod->cardHolderName,
                                $payment->paymentMethod->number
                            ])
                        );
                    } else {
                        $hash = GenerationUtils::generateHash(
                            $config->sharedSecret,
                            implode('.', [
                                $timestamp,
                                $config->merchantId,
                                $payment->customerKey,
                                $paymentKey,
                                $payment->paymentMethod->getShortExpiry(),
                                $payment->paymentMethod->number
                            ])
                        );
                    }
                    $request->appendChild($xml->createElement("orderid", $orderId));
                    $request->appendChild($this->buildCardElement($xml, $payment, $paymentKey));
                    // stored credential
                    if ($payment->storedCredential != null) {
                        $storedCredential = $xml->createElement("storedcredential");
                        $storedCredential->appendChild($xml->createElement("srd", $payment->storedCredential->schemeId));
                        $request->appendChild($storedCredential);
                    }
                    $request->appendChild($xml->createElement("defaultcard", 1));
                } elseif ($builder->entity instanceof Schedule) {
                    $schedule = $builder->entity;
                    $amount = preg_replace('/[^0-9]/', '', sprintf('%01.2f', $schedule->amount));
                    $frequency = EnumMapping::mapScheduleFrequency(
                        GatewayProvider::GP_ECOM,
                        $schedule->frequency
                    );
                    $hash = GenerationUtils::generateHash(
                        $config->sharedSecret,
                        implode('.', [
                            $timestamp,
                            $config->merchantId,
                            $schedule->id,
                            $amount,
                            $schedule->currency,
                            $schedule->customerKey,
                            $frequency
                        ])
                    );

                    $request->appendChild($xml->createElement("scheduleref", $schedule->id ?? ''));
                    $request->appendChild($xml->createElement("alias",$schedule->name ?? ''));
                    $request->appendChild($xml->createElement("orderidstub",$schedule->orderPrefix ?? ''));
                    $request->appendChild($xml->createElement("transtype","auth"));
                    $request->appendChild($xml->createElement("schedule", $frequency ?? ''));
                    if (!empty($schedule->startDate)) {
                        $request->appendChild($xml->createElement("startdate",$schedule->startDate->format('Ymd')));
                    }
                    $request->appendChild($xml->createElement("numtimes",$schedule->numberOfPaymentsRemaining ?? ''));
                    if (!empty($schedule->endDate)) {
                        $request->appendChild($xml->createElement("enddate",$schedule->endDate->format('Ymd')));
                    }
                    $request->appendChild($xml->createElement("payerref",$schedule->customerKey ?? ''));
                    $request->appendChild($xml->createElement("paymentmethod",$schedule->paymentKey ?? ''));
                    $amount = $xml->createElement("amount", $amount);
                    $amount->setAttribute("currency", $schedule->currency ?? '');
                    $request->appendChild($amount);
                    $request->appendChild($xml->createElement("prodid",$schedule->productId ?? ''));
                    $request->appendChild($xml->createElement("varref",$schedule->poNumber ?? ''));
                    $request->appendChild($xml->createElement("custno",$schedule->customerNumber ?? ''));
                    $request->appendChild($xml->createElement("comment",$schedule->description ?? ''));
                }

                //set hash value
                $request->appendChild($xml->createElement($shaHashTagName, $hash));
                break;
            case TransactionType::DELETE:
                if ($builder->entity instanceof RecurringPaymentMethod) {
                    $request->appendChild($xml->createElement("channel", $config->channel ?? ''));
                    if ($config->accountId !== null) {
                        $request->appendChild($xml->createElement("account", $config->accountId));
                    }
                    $payment = $builder->entity;
                    $paymentKey = (!empty($payment->key)) ? $payment->key : $payment->id;
                    $cardElement = $xml->createElement("card");
                    $cardElement->appendChild($xml->createElement("ref", $paymentKey));
                    $cardElement->appendChild($xml->createElement("payerref", $payment->customerKey ?? ''));
                    $request->appendChild($cardElement);

                    $hash = GenerationUtils::generateHash(
                        $config->sharedSecret,
                        implode('.', [
                            $timestamp,
                            $config->merchantId,
                            $payment->customerKey,
                            $paymentKey
                        ])
                    );
                } elseif ($builder->entity instanceof Schedule) {
                    $schedule = $builder->entity;
                    $request->appendChild($xml->createElement("scheduleref", $schedule->key ?? ''));
                    $hash = GenerationUtils::generateHash(
                        $config->sharedSecret,
                        implode('.', [
                            $timestamp,
                            $config->merchantId,
                            $schedule->key,
                        ])
                    );
                }
                $request->appendChild($xml->createElement($shaHashTagName, $hash));
                break;
            case TransactionType::FETCH:
                if ($builder->entity instanceof Schedule) {
                        $scheduleRef = $builder->entity->key;
                        $request->appendChild($xml->createElement(
                            'scheduleref',
                            $scheduleRef ?? '')
                        );
                        $hash = GenerationUtils::generateHash(
                            $config->sharedSecret,
                            implode('.', [
                                $timestamp,
                                $config->merchantId,
                                $scheduleRef,
                            ])
                        );

                    $request->appendChild($xml->createElement($shaHashTagName, $hash));
                }
                break;
            case TransactionType::SEARCH:
                if ($builder->entity instanceof Schedule) {
                    $customerKey = $paymentKey = '';
                    if (isset($builder->searchCriteria[SearchCriteria::CUSTOMER_ID])) {
                        $customerKey = $builder->searchCriteria[SearchCriteria::CUSTOMER_ID];
                        $request->appendChild($xml->createElement(
                            'payerref',
                            $customerKey)
                        );
                    }
                    if (isset($builder->searchCriteria[SearchCriteria::PAYMENT_METHOD_KEY])) {
                        $paymentKey = $builder->searchCriteria[SearchCriteria::PAYMENT_METHOD_KEY];
                        $request->appendChild($xml->createElement(
                            'paymentmethod',
                            $paymentKey)
                        );
                    }
                    $hash = GenerationUtils::generateHash(
                        $config->sharedSecret,
                        implode('.', [
                            $timestamp,
                            $config->merchantId,
                            $customerKey,
                            $paymentKey
                        ])
                    );

                    $request->appendChild($xml->createElement($shaHashTagName, $hash));
                }
                break;
            default:
                break;
        }

        return new Request('', 'POST', $xml->saveXML($request));
    }

    private function buildCustomer($xml, $builder)
    {
        $customer = $builder->entity;
        $type = 'Retail';
        if ($builder->transactionType === TransactionType::EDIT) {
            $type = 'Subscriber';
        }
        $payer = $xml->createElement("payer");
        $payer->setAttribute("ref", (!empty($customer->key)) ? $customer->key :
            GenerationUtils::generateRecurringKey());
        $payer->setAttribute("type", $type);

        $payer->appendChild($xml->createElement("title", $customer->title ?? ''));
        $payer->appendChild($xml->createElement("firstname", $customer->firstName ?? ''));
        $payer->appendChild($xml->createElement("surname", $customer->lastName ?? ''));
        $payer->appendChild($xml->createElement("company", $customer->company ?? ''));


        if ($customer->address != null) {
            $address = $xml->createElement("address");
            $address->appendChild($xml->createElement("line1", $customer->address->streetAddress1 ?? ''));
            $address->appendChild($xml->createElement("line2", $customer->address->streetAddress2 ?? ''));
            $address->appendChild($xml->createElement("line3", $customer->address->streetAddress3 ?? ''));
            $address->appendChild($xml->createElement("city", $customer->address->city ?? ''));
            $address->appendChild($xml->createElement("county", $customer->address->getProvince() ?? ''));
            $address->appendChild($xml->createElement("postcode", $customer->address->postalCode ?? ''));

            $country = $xml->createElement("country", $customer->address->country ?? '');
            if (!empty($customer->address->countryCode)) {
                $country->setAttribute("code", $customer->address->countryCode);
            }
            $address->appendChild($country);

            $payer->appendChild($address);
        }

        $phonenumbers = $xml->createElement("phonenumbers");
        $phonenumbers->appendChild($xml->createElement("home", $customer->homePhone ?? ''));
        $phonenumbers->appendChild($xml->createElement("work", $customer->workPhone ?? ''));
        $phonenumbers->appendChild($xml->createElement("fax", $customer->fax ?? ''));
        $phonenumbers->appendChild($xml->createElement("mobile", $customer->mobilePhone ?? ''));

        $payer->appendChild($phonenumbers);
        $payer->appendChild($xml->createElement("email", $customer->email ?? ''));

        return $payer;
    }

    private function buildCardElement($xml, $payment, $paymentKey = '')
    {
        $card = $payment->paymentMethod;
        $cardElement = $xml->createElement("card");
        $cardElement->appendChild($xml->createElement("ref", $paymentKey));
        $cardElement->appendChild($xml->createElement("payerref", $payment->customerKey ?? ''));
        $cardElement->appendChild($xml->createElement("number", $card->number ?? ''));
        $cardElement->appendChild($xml->createElement("expdate", $card->getShortExpiry() ?? ''));
        $cardElement->appendChild($xml->createElement("chname", $card->cardHolderName ?? ''));
        $cardElement->appendChild($xml->createElement("type", strtoupper($card->getCardType() ?? '')));

        return $cardElement;
    }

    /**
     * Maps a transaction builder to a GP-ECOM request type
     *
     * @param RecurringBuilder $builder Transaction builder
     *
     * @return string
     */
    private function mapRecurringRequestType(RecurringBuilder $builder)
    {
        $entity = $builder->entity;

        switch ($builder->transactionType) {
            case TransactionType::CREATE:
                if ($entity instanceof Customer) {
                    return "payer-new";
                } elseif ($entity instanceof RecurringPaymentMethod) {
                    return "card-new";
                } elseif ($entity instanceof Schedule) {
                    return "schedule-new";
                }
                break;
            case TransactionType::EDIT:
                if ($entity instanceof Customer) {
                    return "payer-edit";
                } elseif ($entity instanceof RecurringPaymentMethod) {
                    return "card-update-card";
                }
                break;
            case TransactionType::DELETE:
                if ($entity instanceof RecurringPaymentMethod) {
                    return "card-cancel-card";
                } elseif ($entity instanceof Schedule) {
                    return "schedule-delete";
                }
                break;
            case TransactionType::FETCH:
                if ($entity instanceof Schedule) {
                    return "schedule-get";
                }
                break;
            case TransactionType::SEARCH:
                if ($entity instanceof Schedule) {
                    return "schedule-search";
                }
                break;
            default:
                break;
        }
        throw new UnsupportedTransactionException(
            'The selected gateway does not support this transaction type.'
        );
    }
}