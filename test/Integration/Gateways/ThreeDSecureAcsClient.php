<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways;

use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\ThreeDSecure;

class ThreeDSecureAcsClient
{
    /**
     * @var string
     */
    private $serviceUrl;

    private $gatewayProvider;

    public $authenticationResultCode;

    public function getGatewayProvider()
    {
        return $this->gatewayProvider;
    }

    public function setGatewayProvider($value)
    {
        $this->gatewayProvider = $value;
    }

    public function __construct($url)
    {
        $this->serviceUrl = $url;
    }

    public function authenticate_v2(ThreeDSecure $secureEcom)
    {
        $kvps = [];
        switch ($this->gatewayProvider)
        {
            case GatewayProvider::GP_API:
                array_push($kvps, ['key' => $secureEcom->messageType, 'value' => urlencode($secureEcom->payerAuthenticationRequest)]);
                $postData = $this->buildData($kvps);
                $header = [
                    "Content-Type: application/x-www-form-urlencoded",
                    "cache-control: no-cache"
                ];
                $verb = 'POST';
                $this->sendRequest($verb, $postData, $header);
                $kvps = [];
                array_push($kvps, ['key' => 'get-status-type', 'value' => "true"]);
                do {
                    $postData = $this->buildData($kvps);
                    $rawResponse = $this->sendRequest($verb, $postData, $header);
                    sleep(5);
                } while (trim($rawResponse) == 'IN_PROGRESS');
                $rawResponse = $this->sendRequest($verb, '', $header);

                $kvps = [];
                $cres = $this->getInputValue($rawResponse, 'cres');
                array_push($kvps, ['key' => 'cres', 'value' => urlencode($cres)]);
                $acsDecodedRS = json_decode(base64_decode($cres), true);
                $postData = $this->buildData($kvps);
                $this->serviceUrl = $this->getInputValue($rawResponse, null, 'ResForm');
                $rawResponse = $this->sendRequest($verb, $postData, $header);
                $rValue = new AcsResponse();
                $status = false;
                if ($this->isJson($rawResponse)) {
                    $rawResponse = json_decode($rawResponse);
                    $status = !empty($rawResponse->success) ? $rawResponse->success : false;
                }
                $rValue->setStatus($status);
                if (!empty($acsDecodedRS['threeDSServerTransID'])) {
                    $rValue->setMerchantData($acsDecodedRS['threeDSServerTransID']);
                }
                break;
            default:
                return false;
        }

        return $rValue;
    }

    public function authenticate_v1(ThreeDSecure $secureEcom)
    {
        $kvps = [];
        switch ($this->gatewayProvider)
        {
            case GatewayProvider::GP_API:
                $header = [
                    "Content-Type: application/x-www-form-urlencoded",
                    "cache-control: no-cache"
                ];
                $verb = 'POST';
                array_push($kvps, ['key' => 'TermUrl', 'value' => urlencode($secureEcom->challengeReturnUrl)]);
                array_push($kvps, ['key' => $secureEcom->sessionDataFieldName , 'value' => $secureEcom->serverTransactionId]);
                array_push($kvps, ['key' => $secureEcom->messageType, 'value' => urlencode($secureEcom->payerAuthenticationRequest)]);
                array_push($kvps, ['key' => 'AuthenticationResultCode', 'value' => $this->authenticationResultCode]);

                $postData = $this->buildData($kvps);
                $rawResponse = $this->sendRequest($verb, $postData, $header);
                $kvps = [];
                $paRes = $this->getInputValue($rawResponse, 'PaRes');
                array_push($kvps, ['key' => 'PaRes', 'value' => urlencode($paRes)]);
                array_push($kvps, ['key' => 'MD', 'value' => $this->getInputValue($rawResponse, 'MD')]);
                $postData = $this->buildData($kvps);
                $this->serviceUrl = $this->getInputValue($rawResponse, null, 'PAResForm');
                $rawResponse2 = $this->sendRequest($verb, $postData, $header);

                $rValue = new AcsResponse();
                if ($this->isJson($rawResponse2)) {
                    $rawResponse2 = json_decode($rawResponse2);
                    $rValue->setStatus(!empty($rawResponse2->success) ? $rawResponse2->success : false);
                    $rValue->setAuthResponse($paRes);
                    $rValue->setMerchantData($this->getInputValue($rawResponse, 'MD'));
                }
                break;
            default:
                return false;
        }

        return $rValue;
    }

    /**
     * @return AcsResponse
     */
    public function authenticate($payerAuthRequest, $merchantData = '')
    {
        $kvps = [];
        switch ($this->gatewayProvider)
        {
            case GatewayProvider::GP_ECOM:
                array_push($kvps, array('key'=>'PaReq', 'value'=> urlencode($payerAuthRequest)));
                array_push($kvps, array('key'=>'TermUrl', 'value'=> urlencode('https://www.mywebsite.com/process3dSecure')));
                array_push($kvps, array('key'=>'MD', 'value'=> urlencode($merchantData)));
                $postData = $this->buildData($kvps);
                $header = [
                    "Content-Type: application/x-www-form-urlencoded",
                    "cache-control: no-cache"
                ];
                $verb = 'POST';
                $rawResponse = $this->sendRequest($verb, $postData, $header);

                $rValue = new AcsResponse();
                $rValue->setAuthResponse($this->getInputValue($rawResponse, 'PaRes'));
                $rValue->setMerchantData($this->getInputValue($rawResponse, 'MD'));
                break;
            default:
                return false;
        }

        return !empty($rValue) ? $rValue : false;
    }

    /**
     * @param string $verb
     * @param string $data
     * @param array $headers
     * @return string
     * @throws ApiException
     */
    private function sendRequest($verb, $data, $headers = [])
    {
        try {
            $request = curl_init();
            curl_setopt_array($request, [
                CURLOPT_URL => $this->serviceUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => $verb,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $data,
                CURLOPT_ENCODING => "",
                CURLOPT_HTTPHEADER => $headers,
            ]);

            $rawResponse = curl_exec($request);
            $curlInfo = curl_getinfo($request);
            $err = curl_error($request);
            curl_close($request);

            if ($curlInfo['http_code'] != 200) {
                throw new ApiException(sprintf('Acs request failed with response code: %s', $curlInfo['http_code']));
            }
        } catch (Exception $exc) {
            throw new ApiException($exc);
        }

        return !empty($rawResponse) ? $rawResponse : '';
    }


    /**
     * @return string
     */
    private function buildData($kvps)
    {
        $result = '';
        $first = true;
        foreach ($kvps as $kvp) {
            if ($first) {
                $first = false;
            } else {
                $result .= '&';
            }

            $result .= $kvp['key'];
            $result .= '=';
            $result .= $kvp['value'];
        }

        return $result;
    }

    /**
     * @return string
     */
    private function getInputValue($raw, $inputValue, $formName = '')
    {
        if ($raw == null) {
            return null;
        }
        if (!empty($inputValue)) {
            $searchString = sprintf('name="%s" value="', $inputValue);
        }
        if (!empty($formName)) {
            $searchString = sprintf('name="%s" action="', $formName);
        }
        $index = strpos($raw, $searchString);

        if ($index > -1) {
            $index = $index + strlen($searchString);

            $length = strpos(substr($raw, $index), '"');

            return substr($raw, $index, $length);
        }
        return null;
    }

    public function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}
