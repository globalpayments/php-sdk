<?php

namespace GlobalPayments\Api\Entities\GpApi;

use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Builders\BaseBuilder;
use GlobalPayments\Api\Entities\EncryptionData;
use GlobalPayments\Api\Entities\Enums\GpApi\CaptureMode;
use GlobalPayments\Api\Entities\Enums\GpApi\EntryMode;
use GlobalPayments\Api\Entities\Enums\StoredCredentialInitiator;
use GlobalPayments\Api\Entities\Enums\Target;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\GpApi\DTO\Card;
use GlobalPayments\Api\Entities\GpApi\DTO\PaymentMethod;
use GlobalPayments\Api\Entities\IRequestBuilder;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\CreditTrackData;
use GlobalPayments\Api\PaymentMethods\DebitTrackData;
use GlobalPayments\Api\PaymentMethods\Interfaces\ICardData;
use GlobalPayments\Api\PaymentMethods\Interfaces\IEncryptable;
use GlobalPayments\Api\PaymentMethods\Interfaces\ITokenizable;
use GlobalPayments\Api\PaymentMethods\Interfaces\ITrackData;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Utils\CardUtils;
use GlobalPayments\Api\Utils\GenerationUtils;
use GlobalPayments\Api\Utils\StringUtils;

class GpApiAuthorizationRequestBuilder implements IRequestBuilder
{
    /***
     * @param AuthorizationBuilder $builder
     *
     * @return bool
     */
    public static function canProcess($builder)
    {
        if ($builder instanceof AuthorizationBuilder) {
            return true;
        }

        return false;
    }

    /**
     * @param BaseBuilder $builder
     * @param GpApiConfig $config
     * @return GpApiRequest|string
     */
    public function buildRequest(BaseBuilder $builder, $config)
    {
        $requestData = null;
        switch ($builder->transactionType) {
            case TransactionType::SALE:
            case TransactionType::REFUND:
            case TransactionType::AUTH:
                $endpoint = GpApiRequest::TRANSACTION_ENDPOINT;
                $verb = 'POST';
                $requestData =  $this->createFromAuthorizationBuilder($builder, $config);
                break;
            case TransactionType::VERIFY:
                if (
                    $builder->requestMultiUseToken &&
                    substr($builder->paymentMethod->token, 0, 4) != PaymentMethod::PAYMENT_METHOD_TOKEN_PREFIX
                ) {
                    $endpoint = GpApiRequest::PAYMENT_METHODS_ENDPOINT;
                    $verb = 'POST';
                    $requestData = [];
                    $requestData['account_name'] = $config->accessTokenInfo->tokenizationAccountName;
                    $requestData['name'] = $builder->description ? $builder->description : "";
                    $requestData['reference'] = $builder->clientTransactionId ?
                        $builder->clientTransactionId : GenerationUtils::generateOrderId();
                    $requestData['usage_mode'] = $builder->paymentMethodUsageMode;
                    $card = new Card();
                    $builderCard = $builder->paymentMethod;
                    $card->number = $builderCard->number;
                    $card->expiry_month = (string)$builderCard->expMonth;
                    $card->expiry_year = substr(str_pad($builderCard->expYear, 4, '0', STR_PAD_LEFT), 2, 2);
                    $card->cvv =$builderCard->cvn;
                    $requestData['card'] = $card;
                } else {
                    $endpoint = GpApiRequest::VERIFICATIONS_ENDPOINT;
                    $verb = 'POST';
                    $requestData = $this->generateVerificationRequest($builder, $config);
                }
                break;
            default:
                return '';
        }

        return new GpApiRequest($endpoint, $verb, $requestData);
    }

    private function generateVerificationRequest(AuthorizationBuilder $builder, GpApiConfig $config)
    {
        $requestBody = [];
        $requestBody['account_name'] = $config->accessTokenInfo->transactionProcessingAccountName;
        $requestBody['channel'] = $config->channel;
        $requestBody['reference'] = !empty($builder->clientTransactionId) ?
            $builder->clientTransactionId : GenerationUtils::getGuid();
        $requestBody['currency'] = $builder->currency;
        $requestBody['country'] = !empty($builder->billingAddress->country) ?
            $builder->billingAddress->country : $config->country;
        $requestBody['payment_method'] = $this->createPaymentMethodParam($builder);

        return $requestBody;
    }

    private function createFromAuthorizationBuilder($builder, GpApiConfig $config)
    {
        $captureMode = $this->getCaptureMode($builder);

        $requestBody = [];
        $requestBody['account_name'] = $config->accessTokenInfo->transactionProcessingAccountName;
        $requestBody['channel'] = $config->channel;
        $requestBody['country'] = !empty($builder->billingAddress->country) ?
            $builder->billingAddress->country : $config->country;
        $requestBody['type'] = ($builder->transactionType == TransactionType::REFUND ? 'REFUND' : 'SALE');
        $requestBody['capture_mode'] = !empty($captureMode) ? $captureMode : CaptureMode::AUTO;
        $requestBody['authorization_mode'] = !empty($builder->allowPartialAuth) ? 'PARTIAL' : null;
        $requestBody['amount'] = StringUtils::toNumeric($builder->amount);
        $requestBody['currency'] = $builder->currency;
        $requestBody['reference'] = !empty($builder->clientTransactionId) ?
            $builder->clientTransactionId : GenerationUtils::getGuid();
        $requestBody['description'] = $builder->description;
        $requestBody['order'] = ['reference' => $builder->orderId];
        $requestBody['gratuity_amount'] = StringUtils::toNumeric($builder->gratuity);
        $requestBody['surcharge_amount'] = StringUtils::toNumeric($builder->surchargeAmount);
        $requestBody['convenience_amount'] = StringUtils::toNumeric($builder->convenienceAmount);
        $requestBody['cashback_amount'] = StringUtils::toNumeric($builder->cashBackAmount);
        $requestBody['ip_address'] = $builder->customerIpAddress;
        $requestBody['payment_method'] = $this->createPaymentMethodParam($builder);

        if (!empty($builder->storedCredential)) {
            $requestBody['initiator'] =
                !empty(StoredCredentialInitiator::$mapInitiator[$builder->storedCredential->initiator]) ?
                    strtoupper(StoredCredentialInitiator::$mapInitiator[$builder->storedCredential->initiator][Target::GP_API]) : '';
            $requestBody['stored_credential'] = [
                'model' => strtoupper($builder->storedCredential->type),
                'reason' => strtoupper($builder->storedCredential->reason),
                'sequence' => strtoupper($builder->storedCredential->sequence)
            ];
        }

        return $requestBody;
    }

    /**
     * @param AuthorizationBuilder $builder
     *
     * @return PaymentMethod
     */
    private function createPaymentMethodParam($builder)
    {
        /** @var CreditCardData|CreditTrackData|DebitTrackData $paymentMethodContainer */
        $paymentMethodContainer = $builder->paymentMethod;
        $paymentMethod = new PaymentMethod();
        $paymentMethod->entry_mode = $this->getEntryMode($builder);

        //authentication
        if ($paymentMethodContainer instanceof CreditCardData) {
            $paymentMethod->name = $paymentMethodContainer->cardHolderName;
            $secureEcom = $paymentMethodContainer->threeDSecure;
            if (!empty($secureEcom)) {
                $threeDS = [
                    'message_version' => $secureEcom->getVersion(),
                    'eci' => $secureEcom->eci,
                    'value' => $secureEcom->authenticationValue,
                    'server_trans_ref' => $secureEcom->serverTransactionId,
                    'ds_trans_ref' => $secureEcom->directoryServerTransactionId
                ];
                $paymentMethod->authentication = ['three_ds' => $threeDS];
            }
        }

        //encryption
        if ($paymentMethodContainer instanceof IEncryptable) {
            if (!empty($paymentMethodContainer->encryptionData)) {
                /**
                 * @var EncryptionData $encryptionData
                 */
                $encryptionData = $paymentMethodContainer->encryptionData;
                $encryption = ['version' => $encryptionData->version];
                if (!empty($encryptionData->ktb)) {
                    $method = 'KBT';
                    $info = $encryptionData->ktb;
                } elseif (!empty($encryptionData->ksn)) {
                    $method = 'KSN';
                    $info = $encryptionData->ksn;
                }
                if (!empty($info)) {
                    $encryption->method = $method;
                    $encryption->info = $info;
                    $paymentMethod->encryption = $encryption;
                }
            }
        }

        if ($paymentMethodContainer instanceof ITokenizable && !empty($paymentMethodContainer->token)) {
            $paymentMethod->id = $paymentMethodContainer->token;
        }

        if (is_null($paymentMethod->id)) {
            $paymentMethod->card = CardUtils::generateCard($builder);
        }
        $paymentMethod->storage_model = $builder->requestMultiUseToken == true ? 'ON_SUCCESS' : null;

        return $paymentMethod;
    }

    private function getEntryMode(AuthorizationBuilder $builder)
    {
        if ($builder->paymentMethod instanceof ICardData) {
            if ($builder->paymentMethod->readerPresent) {
                return $builder->paymentMethod->cardPresent ? EntryMode::MANUAL : EntryMode::IN_APP;
            } else {
                return $builder->paymentMethod->cardPresent ? EntryMode::MANUAL : EntryMode::ECOM;
            }
        } elseif ($builder->paymentMethod instanceof ITrackData) {
            if (!empty($builder->tagData)) {
                return ($builder->paymentMethod->entryMethod == EntryMode::SWIPE) ?
                    EntryMode::CHIP : EntryMode::CONTACTLESS_CHIP;
            } elseif (!empty($builder->hasEmvFallbackData())) {
                return EntryMode::CONTACTLESS_SWIPE;
            } else {
                return EntryMode::SWIPE;
            }
        }

        return EntryMode::ECOM;
    }

    private function getCaptureMode(AuthorizationBuilder $builder)
    {
        if ($builder->multiCapture) {
            return CaptureMode::MULTIPLE;
        }
        if ($builder->transactionType == TransactionType::AUTH) {
            return CaptureMode::LATER;
        }
        return CaptureMode::AUTO;
    }
}