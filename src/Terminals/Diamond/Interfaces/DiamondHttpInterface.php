<?php

namespace GlobalPayments\Api\Terminals\Diamond\Interfaces;

use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Terminals\Abstractions\IDeviceCommInterface;
use GlobalPayments\Api\Terminals\Abstractions\IDeviceMessage;
use GlobalPayments\Api\Terminals\DiamondCloudConfig;
use GlobalPayments\Api\Terminals\TerminalUtils;
use GlobalPayments\Api\Utils\ArrayUtils;
use GlobalPayments\Api\Utils\StringUtils;

class DiamondHttpInterface implements IDeviceCommInterface
{
    private DiamondCloudConfig $settings;
    protected array $headers;
    protected string $contentType = "application/json";
    /** For initial requests: POS ID and for secondary requests and queries: Cloud Transaction ID */
    private string $authorizationId;

    public function __construct(DiamondCloudConfig $settings)
    {
        $this->settings = $settings;
    }

    public function connect()
    {
        $data = strrev($this->settings->isvID) . $this->authorizationId;
        $authorizationToken = hash_hmac("sha256", $data, str_repeat($this->settings->secretKey, 7));
        $this->headers['Authorization'] = sprintf('Bearer %s', $authorizationToken);
    }

    /**
     * @param IDeviceMessage $message
     * @param null $requestType
     */
    public function send($message, $requestType = null)
    {
        $buffer = $message->getSendBuffer();
        $queryParams = $buffer['queryParams'] ?? [];
        if (empty($queryParams['cloud_id'])) {
            $this->authorizationId = $queryParams['POS_ID'];
            unset($queryParams['POS_ID']);
        } else {
            $this->authorizationId = $queryParams['cloud_id'];
            unset($queryParams['cloud_id']);
        }
        $this->connect();
        $queryString = $this->buildQueryString($queryParams);
        $data = $buffer['body'] ?? null;
        $data = ArrayUtils::array_remove_empty($data);
        $data = json_encode($data, JSON_UNESCAPED_SLASHES|JSON_FORCE_OBJECT);
        $verb = $buffer['verb'] ?? 'POST';
        $mandatoryHeaders = [
            'Content-Type' => sprintf('%s', $this->contentType),
            'Content-Length' => empty($data) ? 0 : strlen($data),
        ];
        $headers = $this->prepareHeaders($mandatoryHeaders);
        $options = $this->getCurlOptions();
        try {
            $url = $this->settings->serviceUrl . $buffer['endpoint'] . $queryString;
            $request = curl_init($url);
            curl_setopt_array($request, $options);
            curl_setopt($request, CURLOPT_CUSTOMREQUEST, strtoupper($verb));
            curl_setopt($request, CURLOPT_POSTFIELDS, $data);
            curl_setopt($request, CURLOPT_HTTPHEADER, $headers);
            if (!defined('CURL_SSLVERSION_TLSv1_2')) {
                define('CURL_SSLVERSION_TLSv1_2', 6);
            }
            curl_setopt($request, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
            $curlResponse = curl_exec($request);
            $curlInfo = curl_getinfo($request);
            $header_size = curl_getinfo($request, CURLINFO_HEADER_SIZE);
            $body = substr($curlResponse, $header_size);
            if (!curl_errno($request)) {
                TerminalUtils::manageLog($this->settings->logManagementProvider, $url);
                TerminalUtils::manageLog($this->settings->logManagementProvider, json_encode($headers, JSON_UNESCAPED_SLASHES));
                TerminalUtils::manageLog($this->settings->logManagementProvider, $data);
                TerminalUtils::manageLog($this->settings->logManagementProvider, $curlResponse);
                if ($curlInfo['http_code'] !== 200) {
                    throw new ApiException(sprintf('ERROR: status code  %s', $curlInfo['http_code']));
                }
                if (StringUtils::isJson($body)) {
                    $parsed = json_decode($body);
                    if (isset($parsed->status) && $parsed->status == 'error') {
                        throw new GatewayException(sprintf('Status Code: %s - %s', $parsed->code, $parsed->message));
                    }
                    return $body;
                } else {
                    return $body;
                }
            }
        } catch (\Exception $e) {
            TerminalUtils::manageLog($this->settings->logManagementProvider, $e->getMessage(), true);
            throw new GatewayException('Device '. $this->settings->deviceType . ' error: ' . $e->getMessage(), null, $e->getMessage());
        }
    }

    private function getCurlOptions()
    {
        return [
            CURLOPT_CONNECTTIMEOUT => $this->settings->timeout,
            CURLOPT_TIMEOUT => $this->settings->timeout,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_VERBOSE => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HEADER => true,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS
        ];
    }

    private function buildQueryString(array $queryStringParams = null)
    {
        if (empty($queryStringParams)) {
            return '';
        }
        $query = [];
        foreach ($queryStringParams as $key => $value) {
            $query[] = sprintf('%s=%s', $key, $value);
        }

        return sprintf('?%s', implode('&', $query));
    }

    private function prepareHeaders($mandatoryHeaders)
    {
        $this->headers = array_merge($this->headers, $mandatoryHeaders);

        foreach ($this->headers as $key => $value) {
            if (is_array($value)) {
                $value = implode('; ', $value);
            }
            $headers[] = $key . ': ' . $value;
        }

        return $headers ?? [];
    }

    public function parseResponse($gatewayResponse)
    {
        // TODO: Implement parseResponse() method.
    }


    public function disconnect()
    {
        // TODO: Implement disconnect() method.
    }
}