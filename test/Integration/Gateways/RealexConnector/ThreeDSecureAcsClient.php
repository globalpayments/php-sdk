<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\RealexConnector;

use GlobalPayments\Api\Gateways\Gateway;
use GlobalPayments\Api\Entities\MerchantDataCollection;
use GlobalPayments\Api\Entities\Exceptions\ApiException;

class ThreeDSecureAcsClient
{
    /**
     * @var string
     */
    private $serviceUrl;

    public function __construct($url)
    {
        $this->serviceUrl = $url;
    }

    /**
     * @return AcsResponse
     */
    public function authenticate($payerAuthRequest, $merchantData = '')
    {
        $kvps = [];
        array_push($kvps, array('key'=>'PaReq', 'value'=>$payerAuthRequest));
        array_push($kvps, array('key'=>'TermUrl', 'value'=>'https://www.mywebsite.com/process3dSecure'));
        array_push($kvps, array('key'=>'MD', 'value'=>$merchantData));

        $rawResponse = '';
        try {
            $postData = $this->buildData($kvps);

            $request = curl_init();

            curl_setopt_array($request, array(
            CURLOPT_URL => $this->serviceUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
                "cache-control: no-cache"
            ),
            ));

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

        $rValue = new AcsResponse();
        $rValue->setAuthResponse($this->getInputValue($rawResponse, 'PaRes'));
        $rValue->setMerchantData($this->getInputValue($rawResponse, 'MD'));
        
        return $rValue;
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

            $result .= urlencode($kvp['key']);
            $result .= '=';
            $result .= urlencode($kvp['value']);
        }

        return $result;
    }

    /**
     * @return string
     */
    private function getInputValue($raw, $inputValue)
    {
        if ($raw == null) {
            return null;
        }

        $searchString = sprintf('NAME="%s" VALUE="', $inputValue);

        $index = strpos($raw, $searchString);

        if ($index > -1) {
            $index = $index + strlen($searchString);

            $length = strpos(substr($raw, $index), '"');

            return substr($raw, $index, $length);
        }
        return null;
    }
}
