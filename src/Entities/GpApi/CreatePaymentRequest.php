<?php

namespace GlobalPayments\Api\Entities\GpApi;

use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Builders\ManagementBuilder;
use GlobalPayments\Api\Entities\EncryptionData;
use GlobalPayments\Api\Entities\Enums\GpApi\CaptureMode;
use GlobalPayments\Api\Entities\Enums\StoredCredentialInitiator;
use GlobalPayments\Api\Entities\Enums\Target;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\GpApi\DTO\PaymentMethod;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\CreditTrackData;
use GlobalPayments\Api\PaymentMethods\DebitTrackData;
use GlobalPayments\Api\PaymentMethods\Interfaces\IEncryptable;
use GlobalPayments\Api\PaymentMethods\Interfaces\ITokenizable;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Utils\AmountUtils;
use GlobalPayments\Api\Utils\CardUtils;
use GlobalPayments\Api\Utils\GenerationUtils;
use GlobalPayments\Api\Entities\StoredCredential;
use GlobalPayments\Api\Utils\StringUtils;

class CreatePaymentRequest
{
    public $account_name;
    public $channel;
    public $amount;
    public $currency;
    public $reference;
    public $country;

    public $capture_mode;
    public $type;
    public $authorization_mode;
    public $description;
    public $order_reference;
    public $ip_address;
    /** @var $payment_method PaymentMethod */
    public $payment_method;
    public $initiator;
    public $stored_credential;
    public $gratuity_amount;
    public $surcharge_amount;
    public $convenience_amount;
    public $cashback_amount;

    /**
     * @param AuthorizationBuilder $builder
     * @param GpApiConfig $gpApiConfig
     * @param $entryMode
     * @param $captureMode
     *
     * @return CreatePaymentRequest
     */
    public static function createFromAuthorizationBuilder(
        AuthorizationBuilder $builder,
        GpApiConfig $gpApiConfig,
        $entryMode,
        $captureMode
    ) {
        $paymentRequest = new CreatePaymentRequest();
        $paymentRequest->account_name = $gpApiConfig->getAccessTokenInfo()->getTransactionProcessingAccountName();
        $paymentRequest->type = ($builder->transactionType == TransactionType::REFUND ? 'REFUND' : 'SALE');
        $paymentRequest->channel = $gpApiConfig->getChannel();
        $paymentRequest->capture_mode = !empty($captureMode) ? $captureMode : CaptureMode::AUTO;
        $paymentRequest->authorization_mode = !empty($builder->allowPartialAuth) ? 'PARTIAL' : null;
        $paymentRequest->amount = StringUtils::toNumeric($builder->amount);
        $paymentRequest->currency = $builder->currency;
        $paymentRequest->reference = !empty($builder->clientTransactionId) ?
            $builder->clientTransactionId : GenerationUtils::getGuid();
        $paymentRequest->description = $builder->description;
        $paymentRequest->order_reference = $builder->orderId;
        $paymentRequest->gratuity_amount = StringUtils::toNumeric($builder->gratuity);
        $paymentRequest->surcharge_amount = StringUtils::toNumeric($builder->surchargeAmount);
        $paymentRequest->convenience_amount = StringUtils::toNumeric($builder->convenienceAmount);
        $paymentRequest->cashback_amount = StringUtils::toNumeric($builder->cashBackAmount);
        $paymentRequest->country = !empty($builder->billingAddress) ?
            $builder->billingAddress->country : $gpApiConfig->getCountry();
        $paymentRequest->ip_address = $builder->customerIpAddress;
        $paymentMethod = self::createPaymentMethodParam($builder, $entryMode);

        $paymentRequest->payment_method = $paymentMethod;

        if (!empty($builder->storedCredential)) {
            $paymentRequest->initiator =
                !empty(StoredCredentialInitiator::$mapInitiator[$builder->storedCredential->initiator]) ?
                    strtoupper(StoredCredentialInitiator::$mapInitiator[$builder->storedCredential->initiator][Target::GP_API]) : '';
            $paymentRequest->stored_credential =  (object) [
                'model' => strtoupper($builder->storedCredential->type),
                'reason' => strtoupper($builder->storedCredential->reason),
                'sequence' => strtoupper($builder->storedCredential->sequence)
            ];
        }

        return $paymentRequest;
    }


    /**
     * @param AuthorizationBuilder|ManagementBuilder $builder
     * @param string $entryMode
     *
     * @return PaymentMethod
     */
    public static function createPaymentMethodParam($builder, $entryMode)
    {
        /** @var CreditCardData|CreditTrackData|DebitTrackData $paymentMethodContainer */
        $paymentMethodContainer = $builder->paymentMethod;
        $paymentMethod = new PaymentMethod();
        $paymentMethod->entry_mode = !empty($entryMode) ? $entryMode : null;

        //authentication
        if ($paymentMethodContainer instanceof CreditCardData) {
            $paymentMethod->name = $paymentMethodContainer->cardHolderName;
            $secureEcom = $paymentMethodContainer->threeDSecure;
            if (!empty($secureEcom)) {
                $threeDS = (object) [
                    'message_version' => $secureEcom->getVersion(),
                    'eci' => $secureEcom->eci,
                    'value' => $secureEcom->authenticationValue,
                    'server_trans_ref' => $secureEcom->serverTransactionId,
                    'ds_trans_ref' => $secureEcom->directoryServerTransactionId
                ];
                $paymentMethod->authentication = (object) ['three_ds' => $threeDS];
            }
        }

        //encryption
        if ($paymentMethodContainer instanceof IEncryptable) {
            if (!empty($paymentMethodContainer->encryptionData)) {
                /**
                 * @var EncryptionData $encryptionData
                 */
                $encryptionData = $paymentMethodContainer->encryptionData;
                $encryption = (object) ['version' => $encryptionData->version];
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

        return $paymentMethod;
    }
}
