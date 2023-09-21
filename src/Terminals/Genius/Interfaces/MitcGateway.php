<?php

namespace GlobalPayments\Api\Terminals\Genius\Interfaces;

use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\ServiceEndpoints;
use GlobalPayments\Api\Gateways\{GatewayResponse, RestGateway};
use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Genius\ServiceConfigs\MitcConfig;
use stdClass;

/*
 * "Meet In The Cloud" gateway for Genius devices
 */
class MitcGateway extends RestGateway
{
    private $accountCredentials;
    public $allowKeyEntry;
    private $apiKey;
    private $apiSecret;
    private $appName; // value determined by integrator
    private $appVersion; // value determined by integrator
    private $region;
    private $requestId; // optional; use Version-4 UUID format
    public $targetDevice;
    public $terminalId;

    public function __construct(ConnectionConfig $config)
    {
        parent::__construct();

        $this->formMitcCredentials(
            $config->meetInTheCloudConfig
        );

        // these header names/values should be constant
        $this->headers['X-GP-Api-Key'] = $this->apiKey;
        $this->headers['X-GP-Version'] = '2021-04-08';
        $this->headers['Content-Type'] = 'application/json';
        $this->headers['X-GP-Partner-App-Name'] = $this->appName;
        $this->headers['X-GP-Partner-Version'] = $this->appVersion;
    }

    /**
     * 
     * @param MitcConfig $mitcConfig 
     * @return void 
     */
    private function formMitcCredentials(MitcConfig $mitcConfig) : void
    {   $this->accountCredentials =
            $mitcConfig->xWebId . ':' . $mitcConfig->terminalId . ':' . $mitcConfig->authKey;
        $this->apiKey = $mitcConfig->apiKey;
        $this->apiSecret = $mitcConfig->apiSecret;
        $this->targetDevice = $mitcConfig->targetDevice;
        $this->region = $mitcConfig->region;
        $this->serviceUrl = $mitcConfig->environment === Environment::PRODUCTION ? 
            ServiceEndpoints::MEET_IN_THE_CLOUD_PROD : ServiceEndpoints::MEET_IN_THE_CLOUD_TEST;
        $this->appName = $mitcConfig->appName;
        $this->appVersion = $mitcConfig->appVersion;
        $this->requestId = $mitcConfig->requestId;
        $this->terminalId = $mitcConfig->terminalId;
    }

    /**
     * 
     * @param null|string $message 
     * @param string $endpoint 
     * @param string $verb 
     * @return GatewayResponse 
     * @throws Exception 
     */
    public function send(
        ?string $message, string $endpoint, string $verb
    ) : GatewayResponse
    {
        if (array_key_exists('X-GP-Target-Device', $this->headers)) {
            unset($this->headers['X-GP-Target-Device']);
        }

        $this->headers['X-GP-Request-Id'] = $this->requestId;
        $this->headers['Authorization'] = 'AuthToken ' . $this->generateToken();

        return parent::sendRequest(
            $verb,
            $endpoint,
            $message
        );
   }

   /**
    * 
    * @return string 
    */
    private function generateToken() : string
    {
        $headerObj = new stdClass();
        $headerObj->alg = "HS256";
        $headerObj->typ = "JWT";

        $headerJSON = $this->base64url_encode(json_encode($headerObj));

        // seems to like this format best
        $microseconds = substr(str_replace(".", "", (string) microtime(true)), 0, 13);

        $jwtPayloadObj = new stdClass();
        $jwtPayloadObj->account_credential = $this->accountCredentials;
        $jwtPayloadObj->region = $this->region;
        $jwtPayloadObj->type = "AuthTokenV2";
        $jwtPayloadObj->ts = $microseconds;

        $payloadJSON = $this->base64url_encode(json_encode($jwtPayloadObj));

        $signature = $this->base64url_encode(hash_hmac('sha256', "{$headerJSON}.{$payloadJSON}", $this->apiSecret, true));

        return "{$headerJSON}.{$payloadJSON}.{$signature}";
    }

    private function base64url_encode($data) : string
    {
        $newString = base64_encode($data);
        $newString = strtr($newString, "/=+$/", "");

        return strtr($newString, '+/', '-_');
    }
}
