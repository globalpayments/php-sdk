<?php

namespace GlobalPayments\Api\Gateways;

use Exception;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\IRequestLogger;
use GlobalPayments\Api\Entities\IWebProxy;

abstract class Gateway
{
    /**
     * @var string
     */
    protected $contentType;

    /**
     * @var array<string,string>
     */
    public $headers;

    /**
     * @var integer|string
     */
    public $timeout;

    /**
     * @var string
     */
    public $serviceUrl;

    /**
     * @var array<integer,string>
     */
    public $curlOptions;

    /**
     * @var $requestLogger IRequestLogger
     */
    public $requestLogger;

    /**
     * @var IWebProxy
     */
    public $webProxy;

    /**
     * @var array
     */
    public $dynamicHeaders;

    /** @var Environment */
    public $environment;

    public array $maskedRequestData;

    /**
     * @param string $contentType
     *
     * @return
     */
    public function __construct($contentType)
    {
        $this->headers = [];
        $this->dynamicHeaders = [];
        $this->contentType = $contentType;
        $this->maskedRequestData = [];
    }

    /**
     * @return array<string,string>
     */
    protected function getHttpOptions()
    {
        return [];
    }
    
    /**
     * 
     * @param mixed $verb 
     * @param mixed $endpoint 
     * @param mixed $data 
     * @param null|array $queryStringParams 
     * @return GatewayResponse 
     * @throws Exception 
     */
    protected function sendRequest(
        $verb,
        $endpoint,
        $data = null,
        ?array $queryStringParams = null
    ) {
        try {
            $queryString = !empty($queryStringParams) ? "?" . http_build_query($queryStringParams) : '';
            $request = curl_init($this->serviceUrl . $endpoint . $queryString);

            $headers = $this->prepareHeaders($data);

            if (isset($this->requestLogger)) {
                $dataLogged = $data;
                if (!empty($data) && !empty($this->maskedRequestData) && $this->environment === Environment::PRODUCTION) {
                    $dataLogged = $this->maskSensitiveData($data);
                }
                $this->requestLogger->requestSent($verb, $this->serviceUrl . $endpoint . $queryString,  $headers, null,  $dataLogged);
            }

            curl_setopt($request, CURLOPT_CONNECTTIMEOUT, $this->timeout);
            curl_setopt($request, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($request, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($request, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($request, CURLOPT_CUSTOMREQUEST, strtoupper($verb));
            curl_setopt($request, CURLOPT_POSTFIELDS, $data);
            curl_setopt($request, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($request, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
            curl_setopt($request, CURLOPT_VERBOSE, false);
            curl_setopt($request, CURLOPT_HEADER, true);

            if (!empty($this->webProxy)) {
                curl_setopt($request, CURLOPT_PROXY, $this->webProxy->uri);
                if (!empty($this->webProxy->username) && !empty($this->webProxy->password)) {
                    curl_setopt($request, CURLOPT_PROXYUSERPWD, $this->webProxy->username . ':', $this->webProxy->password);
                }
            }

            // Define the constant manually for earlier versions of PHP.
            // Disable phpcs here since this constant does not exist until PHP 5.5.19.
            // phpcs:disable
            if (!defined('CURL_SSLVERSION_TLSv1_2')) {
                define('CURL_SSLVERSION_TLSv1_2', 6);
            }
            curl_setopt($request, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
            // phpcs:enable

            if ($this->curlOptions != null && !empty($this->curlOptions)) {
                curl_setopt_array($request, $this->curlOptions);
            }

            $curlResponse = curl_exec($request);
            $curlInfo = curl_getinfo($request);
            $curlError = curl_errno($request);
            $header_size = curl_getinfo($request, CURLINFO_HEADER_SIZE);
            $header = substr($curlResponse, 0, $header_size);
            $body = substr($curlResponse, $header_size);

            $response = new GatewayResponse();
            $response->statusCode = $curlInfo['http_code'];
            $response->rawResponse = $body;
            $response->header = $header;

            if (isset($this->requestLogger)) {
                $this->requestLogger->responseReceived($response);
            }

            return $response;
        } catch (\Exception $e) {
            throw new \Exception(
                "Error occurred while communicating with gateway.",
                $e->getCode(),
                $e
            );
        }
    }

    private function maskSensitiveData($data)
    {
        $json = $this->xmlToJson($data, $isXML);
        foreach ($this->maskedRequestData as $key => $value) {
            $val = $json;
            foreach($fields = explode('.', $key) as $k => $item) {
                if ($k == count($fields) -1 && isset($val->{$item})) {
                    $val->{$item} = $value;
                    break;
                }
                if (!isset($val->$item)) {
                    break;
                }
                $val = $val->$item;
            }
        }

        return $isXML === true ? $json->asXML() : json_encode($json);

    }

    public function xmlToJson($data, &$isXML = false)
    {
        $xml = @simplexml_load_string($data);
        if ($xml === false) {
            return json_decode($data);
        }
        $isXML = true;

        return $xml;
    }

    private function prepareHeaders(?string $data) : array
    {
        $mandatoryHeaders = [
            'Content-Type' => sprintf('%s', $this->contentType),
            'Content-Length' => empty($data) ? 0 : strlen($data),
        ];

        $this->headers = array_merge($this->dynamicHeaders, $this->headers, $mandatoryHeaders);

        foreach ($this->headers as $key => $value) {
            if (is_array($value)) {
                $value = implode('; ', $value);
            }
            $headers[] = $key . ': ' . $value;
        }

        return $headers ?? [];
    }
}
