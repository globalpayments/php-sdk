<?php

namespace GlobalPayments\Api\Gateways;

abstract class Gateway
{
    /**
     * @var string
     */
    private $contentType;

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
     * @param string $contentType
     *
     * @return
     */
    public function __construct($contentType)
    {
        $this->headers = [];
        $this->contentType = $contentType;
    }

    /**
     * @return array<string,string>
     */
    protected function getHttpOptions()
    {
        return [];
    }

    /**
     * Uses cURL to communicate with the gateway service
     *
     * @param string $verb
     * @param string $endpoint
     * @param string|null $data
     * @param array<string,string>|null $queryStringParams
     *
     * @throws \Exception
     * @return GatewayResponse
     */
    protected function sendRequest(
        $verb,
        $endpoint,
        $data = null,
        array $queryStringParams = null,
        $headers = []
    ) {
        try {
            $queryString = $this->buildQueryString($queryStringParams);
            $request = curl_init($this->serviceUrl . $endpoint . $queryString);

            $this->headers = array_merge($this->headers, $headers, [
                'Content-Type' => sprintf('%s', $this->contentType),
                'Content-Length' => $data === null ? 0 : strlen($data),
            ]);

            $headers = [];
            foreach ($this->headers as $key => $value) {
                $headers[] = $key . ': '. $value;
            }

            curl_setopt($request, CURLOPT_CONNECTTIMEOUT, $this->timeout);
            curl_setopt($request, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false); //true,);
            curl_setopt($request, CURLOPT_SSL_VERIFYHOST, false); //2,);
            curl_setopt($request, CURLOPT_CUSTOMREQUEST, strtoupper($verb));
            curl_setopt($request, CURLOPT_POSTFIELDS, $data);
            curl_setopt($request, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($request, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
            curl_setopt($request, CURLOPT_VERBOSE, false);
            curl_setopt($request, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);

            if ($this->curlOptions != null && !empty($this->curlOptions)) {
                curl_setopt_array($request, $this->curlOptions);
            }

            $curlResponse = curl_exec($request);
            $curlInfo = curl_getinfo($request);
            $curlError = curl_errno($request);

            $response = new GatewayResponse();
            $response->statusCode = $curlInfo['http_code'];
            $response->rawResponse = $curlResponse;
            return $response;
        } catch (\Exception $e) {
            throw new \Exception(
                "Error occurred while communicating with gateway.",
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @param array<string,string>|null $queryStringParams
     *
     * @return string
     */
    private function buildQueryString(array $queryStringParams = null)
    {
        if ($queryStringParams === null) {
            return '';
        }

        $query = [];

        foreach ($queryStringParams as $key => $value) {
            $query[] = sprintf('%s=%s', $key, $value);
        }

        return sprintf('?%s', implode('&', $query));
    }
}
