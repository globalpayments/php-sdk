<?php
namespace GlobalPayments\Api\Tests\Integration\Gateways\RealexConnector\Hpp;

use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServicesConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Customer;
use GlobalPayments\Api\Tests\Data\TestCards;
use GlobalPayments\Api\Utils\GenerationUtils;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Entities\Enums\AddressType;
use GlobalPayments\Api\Entities\DccRateData;
use GlobalPayments\Api\HostedPaymentConfig;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpEcomConfig;

class RealexHppClient
{
    private $sharedSecret;
    private $paymentData;

    public function __construct($sharedSecret)
    {
        $this->sharedSecret = $sharedSecret;
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
        $requestHash = $this->getValue('SHA1HASH');
        $shippingCode = $this->getValue('SHIPPING_CODE');
        $shippingCountry = $this->getValue('SHIPPING_CO');
        $billingCode = $this->getValue('BILLING_CODE');
        $billingCountry = $this->getValue('BILLING_CO');

        $config = new GpEcomConfig();
        $config->merchantId = $merchantId;
        $config->accountId = $account;
        $config->sharedSecret = $this->sharedSecret;
        $config->serviceUrl = 'https://api.sandbox.realexpayments.com/epage-remote.cgi';
        $config->hostedPaymentConfig = new HostedPaymentConfig();
        $config->hostedPaymentConfig->language = "GB";
        $config->hostedPaymentConfig->responseUrl = "http://requestb.in/10q2bjb1";
        $config->hostedPaymentConfig->version = $hppVersion;

        ServicesContainer::configureService($config);

        // create the card object
        $card = new CreditCardData();
        $card->number = '4006097467207025';
        $card->expMonth = 12;
        $card->expYear = TestCards::validCardExpYear();
        $card->cvn = '131';
        $card->cardHolderName = 'James Mason';

        // // check hash
        $hashParam = [
            $timestamp,
            $merchantId,
            $orderId,
            $amount,
            $currency
        ];

        //for stored card

        if (!empty($this->paymentData['OFFER_SAVE_CARD'])) {
            $hashParam[] = (!empty($this->paymentData['PAYER_REF'])) ?
                    $this->paymentData['PAYER_REF'] : null;
            $hashParam[] = (!empty($this->paymentData['PMT_REF'])) ?
                    $this->paymentData['PMT_REF'] : null;
        }
        
        $newHash = GenerationUtils::generateHash(
            $this->sharedSecret,
            implode('.', $hashParam)
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

            $gatewayResponse = $gatewayRequest->execute();
            
            if ($gatewayResponse->responseCode === '00') {
                return $this->convertResponse($gatewayResponse);
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
            $dccValues->currency = $dccInfo['CURRENCY'];
            $dccValues->dccRate = $dccInfo['RATE'];
            $dccValues->amount = $dccInfo['AMOUNT'];

            $gatewayRequest
                    ->withDccRateData($dccValues);
        }
    }

    public function addFraudManagementInfo($gatewayRequest, $orderId)
    {
        if (!empty($this->paymentData['HPP_FRAUD_FILTER_MODE'])) {
            $tssInfo = $this->getValue('TSS_INFO');

            $this->addAddressDetails(
                $gatewayRequest,
                $tssInfo['BILLING_ADDRESS']['CODE'],
                $tssInfo['BILLING_ADDRESS']['COUNTRY'],
                AddressType::BILLING
            );

            $this->addAddressDetails(
                $gatewayRequest,
                $tssInfo['SHIPPING_ADDRESS']['CODE'],
                $tssInfo['SHIPPING_ADDRESS']['COUNTRY'],
                AddressType::SHIPPING
            );

            $gatewayRequest
                    ->withProductId($tssInfo['PRODID']) // prodid
                    ->withClientTransactionId($tssInfo['VARREF']) // varref
                    ->withCustomerId($tssInfo['CUSTNUM']) // custnum
                    ->withCustomerIpAddress($tssInfo['CUSTIPADDRESS'])
                    ->withFraudFilter($this->paymentData['HPP_FRAUD_FILTER_MODE']);
        }
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

        $newHash = GenerationUtils::generateHash(
            $this->sharedSecret,
            implode('.', [
                    $gatewayResponse->timestamp,
                    $merchantId,
                    $gatewayResponse->transactionReference->orderId,
                    $gatewayResponse->responseCode,
                    $gatewayResponse->responseMessage,
                    $gatewayResponse->transactionReference->transactionId,
                    $gatewayResponse->transactionReference->authCode
                        ])
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
            'SHA1HASH' => $newHash,
            'DCC_INFO_REQUST' => $this->getValue('DCC_INFO'),
            'DCC_INFO_RESPONSE' => $gatewayResponse->dccResponseResult,
            'HPP_FRAUD_FILTER_MODE' => $this->getValue('HPP_FRAUD_FILTER_MODE'),
            'TSS_INFO' => $this->getValue('TSS_INFO')
        ];

        return json_encode($response);
    }
}
