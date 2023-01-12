<?php
namespace GlobalPayments\Api\Tests\Integration\Gateways\GpEcomConnector\Hpp;

use GlobalPayments\Api\Entities\AlternativePaymentResponse;
use GlobalPayments\Api\Entities\Enums\AlternativePaymentType;
use GlobalPayments\Api\Entities\Enums\HostedPaymentMethods;
use GlobalPayments\Api\Entities\Enums\ShaHashType;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\FraudRuleCollection;
use GlobalPayments\Api\PaymentMethods\AlternativePaymentMethod;
use GlobalPayments\Api\PaymentMethods\BankPayment;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Tests\Data\TestCards;
use GlobalPayments\Api\Utils\GenerationUtils;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\Entities\DccRateData;
use GlobalPayments\Api\HostedPaymentConfig;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpEcomConfig;
use GlobalPayments\Api\Utils\Logging\Logger;
use GlobalPayments\Api\Utils\Logging\SampleRequestLogger;

class GpEcomHppClient
{
    private $sharedSecret;
    private $paymentData;
    private $shaHashType;

    public function __construct($sharedSecret, $shaHashType = ShaHashType::SHA1)
    {
        $this->sharedSecret = $sharedSecret;
        $this->shaHashType = $shaHashType;
    }

    public function sendRequest($jsonData, $hppVersion = '')
    {
        $this->paymentData = GenerationUtils::decodeJson($jsonData, true, $hppVersion);

        $timestamp = $this->getValue('TIMESTAMP');
        $merchantId = $this->getValue('MERCHANT_ID');
        $account = $this->getValue('ACCOUNT');
        $orderId = $this->getValue('ORDER_ID');
        $amount = $this->getValue('AMOUNT');
        $currency = $this->getValue('CURRENCY');
        $autoSettle = $this->getValue('AUTO_SETTLE_FLAG');
        $shaHashTagName = $this->shaHashType . 'HASH';
        $requestHash = $this->getValue($shaHashTagName);
        $shippingCode = $this->getValue('SHIPPING_CODE');
        $shippingCountry = $this->getValue('SHIPPING_CO');
        $billingCode = $this->getValue('BILLING_CODE');
        $billingCountry = $this->getValue('BILLING_CO');

        $config = new GpEcomConfig();
        $config->merchantId = $merchantId;
        $config->accountId = $account;
        $config->sharedSecret = $this->sharedSecret;
        $config->shaHashType = $this->shaHashType;
        $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';
        //to be uncomment in case you need to log the raw request/response
        $config->requestLogger = new SampleRequestLogger(new Logger("logs"));
        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
        $config->hostedPaymentConfig->responseUrl = "http://requestb.in/10q2bjb1";
        $config->hostedPaymentConfig->version = $hppVersion;

        ServicesContainer::configureService($config);
        // // check hash
        $hashParam = [
            $timestamp,
            $merchantId,
            $orderId,
            $amount,
            $currency
        ];

        // create the card/APM/LPM/OB object
        if (!empty($this->getValue('PM_METHODS'))) {
            $apmTypes = explode("|", $this->getValue('PM_METHODS'));
            if (in_array(HostedPaymentMethods::OB, $apmTypes)) {
                $card = new BankPayment();
                $card->sortCode = $this->getValue('HPP_OB_DST_ACCOUNT_SORT_CODE');
                $card->accountNumber = $this->getValue('HPP_OB_DST_ACCOUNT_NUMBER');
                $card->accountName = $this->getValue('HPP_OB_DST_ACCOUNT_NAME');
                $card->bankPaymentType = $this->getValue('HPP_OB_PAYMENT_SCHEME');
                $card->iban = $this->getValue('HPP_OB_DST_ACCOUNT_IBAN');
                $hashParam = array_merge(
                    $hashParam,
                    [
                        !empty($card->sortCode) ? $card->sortCode : '',
                        !empty($card->accountNumber) ? $card->accountNumber : '',
                        !empty($card->iban) ? $card->iban : '',
                    ]
                );
            } else {
                $apmType = reset($apmTypes);
                $card = new AlternativePaymentMethod($apmType);
                if ($apmType == AlternativePaymentType::PAYPAL) {
                    //cancelUrl for Paypal example
                    $card->cancelUrl =  'https://www.example.com/failure/cancelURL';
                }
                $card->country = $this->getValue('HPP_CUSTOMER_COUNTRY');
                $card->accountHolderName =
                    $this->getValue('HPP_CUSTOMER_FIRSTNAME') . ' '. $this->getValue('HPP_CUSTOMER_LASTNAME') ;
            }
            $card->returnUrl = $this->getValue('MERCHANT_RESPONSE_URL');
            $card->statusUpdateUrl = $this->getValue('HPP_TX_STATUS_URL');
        } else {
            $card = new CreditCardData();
            $card->number = '4006097467207025';
            $card->expMonth = 12;
            $card->expYear = TestCards::validCardExpYear();
            $card->cvn = '131';
            $card->cardHolderName = 'James Mason';
        }

        //for stored card
        if (!empty($this->paymentData['OFFER_SAVE_CARD'])) {
            $hashParam[] = (!empty($this->paymentData['PAYER_REF'])) ?
                    $this->paymentData['PAYER_REF'] : null;
            $hashParam[] = (!empty($this->paymentData['PMT_REF'])) ?
                    $this->paymentData['PMT_REF'] : null;
        }

        if (!empty($this->paymentData['HPP_FRAUDFILTER_MODE'])) {
            $hashParam[] = $this->paymentData['HPP_FRAUDFILTER_MODE'];
        }

        $newHash = GenerationUtils::generateNewHash(
            $this->sharedSecret,
            implode('.', $hashParam),
            $this->shaHashType
        );
        if ($newHash != $requestHash) {
            throw new ApiException("Incorrect hash. Please check your code and the Developers Documentation.");
        }

        // build request
        if ($amount == null) {
            $validate = $this->getValue("VALIDATE_CARD_ONLY");
            if ($validate) {
                $gatewayRequest = $card->verify();
            } else {
                $gatewayRequest = $card->verify()->WithRequestMultiUseToken(true);
            }
        } else {
            $amount = $amount / 100;
            if ($autoSettle) {
                $gatewayRequest = $card->charge($amount);
            } else {
                $gatewayRequest = $card->authorize($amount);
            }
        }

        try {
            $gatewayRequest
                    ->WithCurrency($currency)
                    ->WithOrderId($orderId)
                    ->withTimeStamp($timestamp);

            $this->addAddressDetails($gatewayRequest, $billingCode, $billingCountry, AddressType::BILLING);
            $this->addAddressDetails($gatewayRequest, $shippingCode, $shippingCountry, AddressType::SHIPPING);

            //handle DCC
            $this->addDCCInfo($gatewayRequest, $orderId);

            //handle fraud management
            $this->addFraudManagementInfo($gatewayRequest, $orderId);

            if ($card instanceof BankPayment) {
                $this->addRemittanceRef($gatewayRequest);
            }

            $gatewayResponse = $gatewayRequest->execute();
            if (
                in_array($gatewayResponse->responseCode, ['00', '01']) ||
                ($card instanceof BankPayment && $gatewayResponse->responseMessage == 'PAYMENT_INITIATED')
            ) {
                return $this->convertResponse($gatewayResponse);
            } else {
                throw new ApiException(
                    sprintf('Status Code: %s - %s', $gatewayResponse->responseCode, $gatewayResponse->responseMessage)
                );
            }
        } catch (ApiException $exc) {
            throw $exc;
        }

        return null;
    }

    public function getValue($value)
    {
        if (isset($this->paymentData[$value])) {
            return $this->paymentData[$value];
        }
        return null;
    }

    public function addDCCInfo($gatewayRequest, $orderId)
    {
        if (!empty($this->paymentData['DCC_ENABLE'])) {
            $dccInfo = $this->getValue('DCC_INFO');

            $dccValues = new DccRateData();
            $dccValues->orderId = $orderId;
            $dccValues->dccProcessor = $dccInfo['CCP'];
            $dccValues->dccType = $dccInfo['TYPE'];
            $dccValues->dccRateType = $dccInfo['RATE_TYPE'];
            $dccValues->cardHolderCurrency = $dccInfo['CURRENCY'];
            $dccValues->cardHolderRate = $dccInfo['RATE'];
            $dccValues->cardHolderAmount = $dccInfo['AMOUNT'];

            $gatewayRequest
                    ->withDccRateData($dccValues);
        }
    }

    public function addRemittanceRef($gatewayRequest)
    {

        $gatewayRequest->withRemittanceReference(
            $this->getValue('HPP_OB_REMITTANCE_REF_TYPE'),
            $this->getValue('HPP_OB_REMITTANCE_REF_VALUE')
        );
    }

    public function addFraudManagementInfo($gatewayRequest, $orderId)
    {
        if (!empty($this->paymentData['HPP_FRAUDFILTER_MODE'])) {
            $tssInfo = $this->getValue('TSS_INFO');

            $this->addAddressDetails(
                $gatewayRequest,
                $tssInfo['BILLING_ADDRESS']['CODE'] ?? '',
                $tssInfo['BILLING_ADDRESS']['COUNTRY'] ?? '',
                AddressType::BILLING
            );

            $this->addAddressDetails(
                $gatewayRequest,
                $tssInfo['SHIPPING_ADDRESS']['CODE'] ?? '',
                $tssInfo['SHIPPING_ADDRESS']['COUNTRY'] ?? '',
                AddressType::SHIPPING
            );

            $gatewayRequest
                    ->withProductId($tssInfo['PRODID'] ?? '') // prodid
                    ->withClientTransactionId($tssInfo['VARREF'] ?? '') // varref
                    ->withCustomerId($tssInfo['CUSTNUM'] ?? '') // custnum
                    ->withCustomerIpAddress($tssInfo['CUSTIPADDRESS'] ?? '');

            if (isset($this->paymentData['HPP_FRAUDFILTER_MODE'])) {
                $gatewayRequest
                    ->withFraudFilter($this->paymentData['HPP_FRAUDFILTER_MODE'], $this->getFraudRules());
            }
        }
    }

    public function getFraudRules()
    {
        $hppFraudRules = array_filter($this->paymentData, function($key) {
            return strpos($key, 'HPP_FRAUDFILTER_RULE_') === 0;
        }, ARRAY_FILTER_USE_KEY);
        if (!empty($hppFraudRules)) {
            $fraudFilterRules = new FraudRuleCollection();
            foreach ($hppFraudRules as $hppFraudRuleKey => $hppFraudRuleMode) {
                $fraudFilterRules->addRule(
                    substr($hppFraudRuleKey, strlen('HPP_FRAUDFILTER_RULE_')),
                    $hppFraudRuleMode
                );
            }

        }

        return !empty($fraudFilterRules) ? $fraudFilterRules : null;
    }

    public function addAddressDetails($gatewayRequest, $code, $country, $addressType = AddressType::BILLING)
    {
        if ($code != null || $country != null) {
            $address = new Address();
            $address->postalCode = $code;
            $address->country = $country;

            $gatewayRequest
                    ->WithAddress($address, $addressType);
        }
    }

    public function convertResponse($gatewayResponse)
    {
        $merchantId = $this->paymentData['MERCHANT_ID'];
        $account = $this->paymentData['ACCOUNT'];

        $newHash = GenerationUtils::generateNewHash(
            $this->sharedSecret,
            implode('.', [
                    $gatewayResponse->timestamp,
                    $merchantId,
                    $gatewayResponse->transactionReference->orderId,
                    $gatewayResponse->responseCode,
                    $gatewayResponse->responseMessage,
                    $gatewayResponse->transactionReference->transactionId,
                    $gatewayResponse->transactionReference->authCode
                        ]),
            $this->shaHashType
        );

        // begin building response
        $response = [
            'MERCHANT_ID' => $merchantId,
            'ACCOUNT' => $this->getValue('ACCOUNT'),
            'ORDER_ID' => $gatewayResponse->transactionReference->orderId,
            'TIMESTAMP' => $gatewayResponse->timestamp,
            'RESULT' => $gatewayResponse->responseCode,
            'PASREF' => $gatewayResponse->transactionReference->transactionId,
            'AUTHCODE' => $gatewayResponse->transactionReference->authCode,
            'AVSPOSTCODERESULT' => $gatewayResponse->avsResponseCode,
            'CVNRESULT' => $gatewayResponse->cvnResponseCode,
            'HPP_LANG' => $this->getValue('HPP_LANG'),
            'SHIPPING_CODE' => $this->getValue('SHIPPING_CODE'),
            'SHIPPING_CO' => $this->getValue('SHIPPING_CO'),
            'BILLING_CODE' => $this->getValue('BILLING_CODE'),
            'BILLING_CO' => $this->getValue('BILLING_CO'),
            'ECI' => $this->getValue('ECI'),
            'CAVV' => $this->getValue('CAVV'),
            'XID' => $this->getValue('XID'),
            'MERCHANT_RESPONSE_URL' => $this->getValue('MERCHANT_RESPONSE_URL'),
            'CARD_PAYMENT_BUTTON' => $this->getValue('CARD_PAYMENT_BUTTON'),
            'MESSAGE' => $gatewayResponse->responseMessage,
            'AMOUNT' => $this->getValue('AMOUNT'),
            $this->shaHashType . 'HASH' => $newHash,
            'DCC_INFO_REQUST' => $this->getValue('DCC_INFO'),
            'DCC_INFO_RESPONSE' => $gatewayResponse->dccRateData,
            'HPP_FRAUDFILTER_MODE' => $this->getValue('HPP_FRAUDFILTER_MODE'),
            'HPP_FRAUDFILTER_RESULT' => !empty($gatewayResponse->fraudFilterResponse) ?
                $gatewayResponse->fraudFilterResponse->fraudResponseResult : null
        ];
        if (!empty($gatewayResponse->transactionReference->alternativePaymentResponse)) {
            /** @var AlternativePaymentResponse $alternativePaymentResponse */
            $alternativePaymentResponse = $gatewayResponse->transactionReference->alternativePaymentResponse;
            $apmResponse = [
                'HPP_CUSTOMER_FIRSTNAME' => $this->getValue('HPP_CUSTOMER_FIRSTNAME'),
                'HPP_CUSTOMER_LASTNAME' => $this->getValue('HPP_CUSTOMER_LASTNAME'),
                'HPP_CUSTOMER_COUNTRY' => $this->getValue('HPP_CUSTOMER_COUNTRY'),
                'PAYMENTMETHOD' => $alternativePaymentResponse->providerName,
                'PAYMENTPURPOSE' => $alternativePaymentResponse->paymentPurpose,
                'HPP_CUSTOMER_BANK_ACCOUNT' => $alternativePaymentResponse->bankAccount
            ];
            $response = array_merge($response, $apmResponse);
        }
        if (!empty($gatewayResponse->fraudFilterResponse)) {
            $hppFraudRules = [];
            foreach ($gatewayResponse->fraudFilterResponse->fraudResponseRules as $fraudResponseRule) {
                $hppFraudRules['HPP_FRAUDFILTER_RULE_' .$fraudResponseRule['id']] = $fraudResponseRule['action'];
            }
            $response = array_merge($response, $hppFraudRules);
        }
        $response['TSS_INFO'] = $this->getValue('TSS_INFO');

        return json_encode($response);
    }
}
