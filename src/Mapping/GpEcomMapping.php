<?php

namespace GlobalPayments\Api\Mapping;

use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Builders\ManagementBuilder;
use GlobalPayments\Api\Entities\AlternativePaymentResponse;
use GlobalPayments\Api\Entities\Customer;
use GlobalPayments\Api\Entities\DccRateData;
use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\RecurringSequence;
use GlobalPayments\Api\Entities\Enums\ReportType;
use GlobalPayments\Api\Entities\Enums\ScheduleFrequency;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException;
use GlobalPayments\Api\Entities\FraudManagementResponse;
use GlobalPayments\Api\Entities\RecurringEntity;
use GlobalPayments\Api\Entities\Reporting\TransactionSummary;
use GlobalPayments\Api\Entities\Schedule;
use GlobalPayments\Api\Entities\ThreeDSecure;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\PaymentMethods\RecurringPaymentMethod;
use GlobalPayments\Api\PaymentMethods\TransactionReference;

class GpEcomMapping
{
    /**
     * Deserializes the gateway's XML response
     *
     * @param string $rawResponse The XML response
     *
     * @return Transaction
     */
    public static function mapResponse($root, array $acceptedCodes = null)
    {
        $result = new Transaction();
        self::checkResponse($root, $acceptedCodes);

        $result->responseCode = (string)$root->result;
        $result->responseMessage = (string)$root->message;
        $result->cvnResponseCode = (string)$root->cvnresult;
        $result->avsResponseCode = (string)$root->avspostcoderesponse;
        $result->avsAddressResponse = (string)$root->avsaddressresponse;
        $result->transactionReference = new TransactionReference();
        $result->transactionReference->paymentMethodType = PaymentMethodType::CREDIT;
        $result->transactionReference->transactionId = (string)$root->pasref;
        $result->transactionReference->authCode = (string)$root->authcode;
        $result->transactionReference->orderId = (string)$root->orderid;
        $result->timestamp = (!empty($root->attributes()->timestamp)) ?
            (string) $root->attributes()->timestamp :
            '';

        // 3d secure enrolled
        if (!empty($root->enrolled)) {
            $result->threeDSecure = new ThreeDSecure();
            $result->threeDSecure->enrolled = (string)$root->enrolled;
            $result->threeDSecure->xid = (string)$root->xid;
            $result->threeDSecure->issuerAcsUrl = (string)$root->url;
            $result->threeDSecure->payerAuthenticationRequest = (string)$root->pareq;
        }

        // 3d secure signature
        if (!empty($root->threedsecure)) {
            $secureEcom = new ThreeDSecure();
            $secureEcom->status = (string)$root->threedsecure->status;
            $secureEcom->eci = (string)$root->threedsecure->eci;
            $secureEcom->cavv = (string)$root->threedsecure->cavv;
            $secureEcom->xid = (string)$root->threedsecure->xid;
            $secureEcom->algorithm = (int)$root->threedsecure->algorithm;
            $result->threeDSecure = $secureEcom;
        }

        // stored credential
        $result->schemeId = (string)$root->srd;

        // dccinfo
        if (!empty($root->dccinfo)) {
            $result->dccRateData = new DccRateData();

            $result->dccRateData->cardHolderCurrency = (string)$root->dccinfo->cardholdercurrency;
            $result->dccRateData->cardHolderAmount = (string)$root->dccinfo->cardholderamount;
            $result->dccRateData->cardHolderRate = (string)$root->dccinfo->cardholderrate;
            $result->dccRateData->merchantCurrency = (string)$root->dccinfo->merchantcurrency;
            $result->dccRateData->merchantAmount = (string)$root->dccinfo->merchantamount;
            $result->dccRateData->marginRatePercentage = (string)$root->dccinfo->marginratepercentage;
            $result->dccRateData->exchangeRateSourceName = (string)$root->dccinfo->exchangeratesourcename;
            $result->dccRateData->commissionPercentage = (string)$root->dccinfo->commissionpercentage;
            $result->dccRateData->exchangeRateSourceTimestamp = (string)
            $root->dccinfo->exchangeratesourcetimestamp;
        }

        // fraud filter
        if (!empty($root->fraudresponse)) {
            $fraudResponse = $root->fraudresponse;
            $result->fraudFilterResponse = new FraudManagementResponse();

            foreach ($fraudResponse->attributes() as $attrName => $attrValue) {
                $result->fraudFilterResponse->fraudResponseMode = (!empty($attrValue)) ? (string) $attrValue : '';
            }

            $result->fraudFilterResponse->fraudResponseResult = (!empty($fraudResponse->result)) ?
                (string) $fraudResponse->result : '';

            if (!empty($fraudResponse->rules)) {
                foreach ($fraudResponse->rules->rule as $rule) {
                    $ruleDetails = [
                        'id' => (string) $rule->attributes()->id,
                        'name' => (string) $rule->attributes()->name,
                        'action' => (string) $rule->action
                    ];
                    $result->fraudFilterResponse->fraudResponseRules[] = $ruleDetails;
                }
            }
        }

        // alternativePaymentResponse
        if (!empty($root->paymentmethoddetails)) {
            $result->alternativePaymentResponse = new AlternativePaymentResponse();

            $result->alternativePaymentResponse->paymentMethod = (string)
            $root->paymentmethoddetails->paymentmethod;
            $result->alternativePaymentResponse->bankAccount = (string)
            $root->paymentmethoddetails->bankaccount;
            $result->alternativePaymentResponse->accountHolderName = (string)
            $root->paymentmethoddetails->accountholdername;
            $result->alternativePaymentResponse->country = (string)
            $root->paymentmethoddetails->country;
            $result->alternativePaymentResponse->redirectUrl = (string)
            $root->paymentmethoddetails->redirecturl;
            $result->alternativePaymentResponse->paymentPurpose = (string)
            $root->paymentmethoddetails->paymentpurpose;
            $result->alternativePaymentResponse->providerName = (string) $root->paymentmethod;
            if (!empty($root->paymentmethoddetails->SetExpressCheckoutResponse)) {
                $apmResponseDetails = $root->paymentmethoddetails->SetExpressCheckoutResponse;
            } elseif (!empty($root->paymentmethoddetails->DoExpressCheckoutPaymentResponse)) {
                $apmResponseDetails = $root->paymentmethoddetails->DoExpressCheckoutPaymentResponse;
            }
            if (!empty($apmResponseDetails)) {
                $result->alternativePaymentResponse->sessionToken = !empty($apmResponseDetails->Token) ?
                    (string) $apmResponseDetails->Token : null;
                $result->alternativePaymentResponse->ack = !empty($apmResponseDetails->Ack) ?
                    (string) $apmResponseDetails->Ack : null;
                $result->alternativePaymentResponse->timeCreatedReference = !empty($apmResponseDetails->Timestamp) ?
                    (string) $apmResponseDetails->Timestamp : null;
                $result->alternativePaymentResponse->correlationReference = !empty($apmResponseDetails->CorrelationID) ?
                    (string) $apmResponseDetails->CorrelationID : null;
                $result->alternativePaymentResponse->versionReference = !empty($apmResponseDetails->Version) ?
                    (string) $apmResponseDetails->Version : null;
                $result->alternativePaymentResponse->buildReference = !empty($apmResponseDetails->Build) ?
                    (string) $apmResponseDetails->Build : null;
                if (!empty($apmResponseDetails->PaymentInfo)) {
                    $paymentInfo = $apmResponseDetails->PaymentInfo;
                    $result->alternativePaymentResponse->transactionReference = (string) $paymentInfo->TransactionID;
                    $result->alternativePaymentResponse->paymentType = (string) $paymentInfo->PaymentType;
                    $result->alternativePaymentResponse->paymentTimeReference = (string) $paymentInfo->PaymentDate;
                    $result->alternativePaymentResponse->grossAmount = (string) $paymentInfo->GrossAmount;
                    $result->alternativePaymentResponse->feeAmount = (string) $paymentInfo->TaxAmount;
                    $result->alternativePaymentResponse->paymentStatus = (string) $paymentInfo->PaymentStatus;
                    $result->alternativePaymentResponse->pendingReason = (string) $paymentInfo->PendingReason;
                    $result->alternativePaymentResponse->reasonCode = (string) $paymentInfo->ReasonCode;
                    $result->alternativePaymentResponse->authProtectionEligibilty = (string) $paymentInfo->ProtectionEligibility;
                    $result->alternativePaymentResponse->authProtectionEligibiltyType = (string) $paymentInfo->ProtectionEligibilityType;
                }
            }
        }

        return $result;
    }

    public static function mapScheduleReport($response, $reportType)
    {
        self::checkResponse($response);
        switch ($reportType)
        {
            case TransactionType::FETCH:
                return self::hydrateSchedule($response);
            case TransactionType::SEARCH:
                $scheduleList = [];
                if (!empty($response->schedules)) {
                    foreach ($response->schedules->schedule as $scheduleItem) {
                        $scheduleList[] = self::hydrateSchedule($scheduleItem);
                    }
                }
                return $scheduleList;
        }

        return [];
    }

    public static function mapRecurringEntityResponse($response,RecurringEntity $recurringEntity)
    {
        self::checkResponse($response);
        switch (get_class($recurringEntity))
        {
            case Schedule::class:
                $schedule = $recurringEntity;
                $schedule->scheduletext = $response->scheduletext;
                $schedule->key = $recurringEntity->id;
                $schedule->id = (string) $response->pasref ?? null;
                return $schedule;
            case RecurringPaymentMethod::class:
                /** @var RecurringPaymentMethod $recurringPaymentMethod */
                $recurringPaymentMethod = $recurringEntity;
                $recurringPaymentMethod->key = $recurringEntity->id ?? $recurringEntity->key;
                $recurringPaymentMethod->id = (string) $response->pasref;
                return $recurringPaymentMethod;
            case Customer::class:
                /** @var Customer $customer */
                $customer = $recurringEntity;
                $customer->id = (string) $response->pasref;
                return $customer;
            default:
                throw new UnsupportedTransactionException(
                    sprintf("Unsupported recurring entity mapping %s!", get_class($recurringEntity))
                );
        }
    }

    private static function hydrateSchedule($response)
    {
        $schedule = new Schedule();
        $schedule->id = !empty($response->scheduleref) ? (string)$response->scheduleref : null;
        $schedule->key = !empty($response->scheduleref) ? (string)$response->scheduleref : null;
        $schedule->customerKey = !empty($response->payerref) ? (string)$response->payerref : null;
        $schedule->paymentKey = !empty($response->paymentmethod) ? (string)$response->paymentmethod : null;
        $schedule->orderPrefix = !empty($response->orderidstub) ? (string)$response->orderidstub : null;
        $schedule->amount = !empty($response->amount) ? (string)$response->amount : null;
        $schedule->currency = !empty($response->amount) ? (string)$response->amount['currency'] : null;
        $schedule->frequency = !empty($response->schedule) ? self::mapFrequency($response->schedule) : null ;
        $schedule->productId = !empty($response->prodid) ? (string)$response->prodid : null;
        $schedule->description = !empty($response->comment) ? (string)$response->comment : null;
        $schedule->poNumber = !empty($response->varref) ? (string)$response->varref : null;
        $schedule->startDate = !empty($response->startdate) ? new \DateTime((string)$response->startdate) : '';
        $schedule->endDate = !empty($response->enddate) ? new \DateTime((string)$response->enddate) : '';
        $schedule->numberOfPaymentsRemaining = !empty($response->numtimes) && isset($response->timesrun) ?
            ((int)$response->numtimes - (int)$response->timesrun) : null;
        $schedule->scheduletext = !empty($response->scheduletext) ? (string)$response->scheduletext : null;

        return $schedule;
    }

    private static function mapFrequency($value)
    {
        list($day, $month, $year) = explode(" ", $value);
        if ($day === '?' && $month === '*') {
            return ScheduleFrequency::WEEKLY;
        }
        if ($year === '?') {
            switch ($month) {
                case '*':
                    return ScheduleFrequency::MONTHLY;
                case '*/2':
                    return ScheduleFrequency::BI_MONTHLY;
                case '*/3':
                    return ScheduleFrequency::QUARTERLY;
                case '*/6':
                    return ScheduleFrequency::SEMI_ANNUALLY;
            }
        }

        if (is_numeric($day) && is_numeric($month) && $year === '?') {
            return ScheduleFrequency::ANNUALLY;
        }
    }

    /**
     * Maps a transaction builder to a GP-ECOM request type
     *
     * @param AuthorizationBuilder $builder Transaction builder
     *
     * @return string
     */
    public static function mapAuthRequestType(AuthorizationBuilder $builder)
    {
        switch ($builder->transactionType) {
            case TransactionType::SALE:
            case TransactionType::AUTH:
                if ($builder->paymentMethod->paymentMethodType == PaymentMethodType::CREDIT) {
                    if ($builder->transactionModifier === TransactionModifier::OFFLINE) {
                        return 'offline';
                    } elseif ($builder->transactionModifier === TransactionModifier::ENCRYPTED_MOBILE) {
                        return 'auth-mobile';
                    }
                } elseif ($builder->paymentMethod->paymentMethodType == PaymentMethodType::RECURRING) {
                    return (!empty($builder->recurringSequence) &&
                        $builder->recurringSequence == RecurringSequence::FIRST) ?
                        'auth' :
                        'receipt-in';
                } elseif ($builder->paymentMethod->paymentMethodType == PaymentMethodType::APM) {
                    return "payment-set";
                }
                return 'auth';
            case TransactionType::CAPTURE:
                return 'settle';
            case TransactionType::VERIFY:
                if ($builder->paymentMethod->paymentMethodType == PaymentMethodType::RECURRING) {
                    if (!empty($builder->transactionModifier) &&
                        $builder->transactionModifier === TransactionModifier::SECURE3D) {
                        return 'realvault-3ds-verifyenrolled';
                    }
                    return 'receipt-in-otb';
                }
                return 'otb';
            case TransactionType::REFUND:
                if ($builder->paymentMethod->paymentMethodType == PaymentMethodType::CREDIT) {
                    return 'credit';
                }
                return 'payment-out';
            case TransactionType::DCC_RATE_LOOKUP:
                if ($builder->paymentMethod->paymentMethodType == PaymentMethodType::CREDIT) {
                    return "dccrate";
                }
                return "realvault-dccrate";

            case TransactionType::REVERSAL:
                // TODO: should be customer type
                throw new UnsupportedTransactionException(
                    'The selected gateway does not support this transaction type.'
                );
            case TransactionType::VERIFY_ENROLLED:
                if ($builder->paymentMethod instanceof RecurringPaymentMethod) {
                    return 'realvault-3ds-verifyenrolled';
                }
                return '3ds-verifyenrolled';
            default:
                return 'unknown';
        }
    }

    /**
     * Maps a transaction builder to a GP-ECOM request type
     *
     * @param ManagementBuilder $builder Transaction builder
     *
     * @return string
     */
    public static function mapManageRequestType(ManagementBuilder $builder)
    {
        switch ($builder->transactionType) {
            case TransactionType::CAPTURE:
                return 'settle';
            case TransactionType::HOLD:
                return 'hold';
            case TransactionType::REFUND:
                if ($builder->alternativePaymentType !== null) {
                    return 'payment-credit';
                }
                return 'rebate';
            case TransactionType::RELEASE:
                return 'release';
            case TransactionType::VOID:
            case TransactionType::REVERSAL:
                return 'void';
            case TransactionType::VERIFY_SIGNATURE:
                return '3ds-verifysig';
            case TransactionType::CONFIRM:
                return 'payment-do';
            default:
                return 'unknown';
        }
    }

    public static function mapReportResponse($root, $reportType)
    {
        $summary = new TransactionSummary();

        try {
            self::checkResponse($root);
            switch ($reportType) {
                case ReportType::TRANSACTION_DETAIL:
                    $summary->transactionId = (string)$root->pasref;
                    $summary->orderId = (string)$root->orderid;
                    $summary->authCode = (string)$root->authcode;
                    $summary->maskedCardNumber = (string)$root->cardnumber;
                    $summary->avsResponseCode = (string)$root->avspostcoderesponse;
                    $summary->cvnResponseCode = (string)$root->cvnresult;
                    $summary->gatewayResponseCode = (string)$root->result;
                    $summary->gatewayResponseMessage = (string)$root->message;
                    $summary->batchSequenceNumber = (string)$root->batchid;
                    $summary->gatewayResponseCode = (string)$root->result;
                    if (!empty($root->fraudresponse)) {
                        $summary->fraudRuleInfo = (string)$root->fraudresponse->result;
                    }
                    if (!empty($root->threedsecure)) {
                        $summary->cavvResponseCode = (string)$root->threedsecure->cavv;
                        $summary->eciIndicator = (string)$root->threedsecure->eci;
                        $summary->xid = (string)$root->threedsecure->xid;
                    }
                    if (!empty($root->srd)) {
                        $summary->schemeReferenceData = (string)$root->srd;
                    }
                    break;
                default:
                    break;
            }
        } catch (Exception $e) {
            throw new ApiException($e->getMessage(), $e);
        }
        return $summary;
    }

    protected static function checkResponse($root, array $acceptedCodes = null)
    {
        if ($acceptedCodes === null) {
            $acceptedCodes = [ "00" ];
        }

        $responseCode = (string)$root->result;
        $responseMessage = (string)$root->message;

        if (!in_array($responseCode, $acceptedCodes)) {
            throw new GatewayException(
                sprintf('Unexpected Gateway Response: %s - %s', $responseCode, $responseMessage),
                $responseCode,
                $responseMessage
            );
        }
    }
}