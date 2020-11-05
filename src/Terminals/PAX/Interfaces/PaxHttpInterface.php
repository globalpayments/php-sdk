<?php

namespace GlobalPayments\Api\Terminals\PAX\Interfaces;

use GlobalPayments\Api\Terminals\Interfaces\IDeviceCommInterface;
use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\TerminalUtils;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;
use GlobalPayments\Api\Terminals\PAX\Entities\Enums\PaxMessageId;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;

/*
 * TCP interface for the device connection and parse response
 */

class PaxHttpInterface implements IDeviceCommInterface
{
    /*
     * Device configuration details ConnectionConfig object
     *
     */

    public $deviceDetails;


    /*
     * Device request type
     *
     */
    private $requestType;

    /*
     * @param ConnectionConfig object $config device configuration details
     */

    public function __construct(ConnectionConfig $config)
    {
        $this->deviceDetails = $config;
    }

    public function connect()
    {
    }

    public function disconnect()
    {
    }

    /*
     * Send request message to device using socket connection
     * @param string $message XML request string
     */

    public function send($message, $requestType = null)
    {
        $this->requestType = $requestType;
        $out = '';
        try {
            $protocol = ($this->deviceDetails->connectionMode === ConnectionModes::HTTPS) ? 'https' : 'http';
            $options = $this->getCurlOptions();
            $url = sprintf(
                "%s://%s:%s?%s",
                $protocol,
                $this->deviceDetails->ipAddress,
                $this->deviceDetails->port,
                base64_encode($message)
            );
            
            $request = curl_init($url);
            curl_setopt_array($request, $options);
            $out = curl_exec($request);
            
            if (!empty($out)) {
                TerminalUtils::manageLog($this->deviceDetails->logManagementProvider, $out, true);
                return $out;
            }
        } catch (\Exception $e) {
            TerminalUtils::manageLog($this->deviceDetails->logManagementProvider, $e->getMessage(), true);
            throw new GatewayException('Device error: ' . $e->getMessage(), null, $e->getMessage());
        }
        return;
    }

    public function parseResponse($gatewayRawResponse)
    {
    }

    private function getCurlOptions()
    {
        $config = [
            CURLOPT_CONNECTTIMEOUT => $this->deviceDetails->timeout,
            CURLOPT_TIMEOUT => $this->deviceDetails->timeout,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_VERBOSE => false
        ];
        if ($this->deviceDetails->connectionMode === ConnectionModes::HTTPS) {
            $config[CURLOPT_SSL_VERIFYPEER] = false; //true
            $config[CURLOPT_SSL_VERIFYHOST] = false; //2
            $config[CURLOPT_PROTOCOLS] = CURLPROTO_HTTPS;

            // Define the constant manually for earlier versions of PHP.
            // Disable phpcs here since this constant does not exist until PHP 5.5.19.
            // phpcs:disable
            if (!defined('CURL_SSLVERSION_TLSv1_2')) {
                define('CURL_SSLVERSION_TLSv1_2', 6);
            }
            $config[CURLOPT_SSLVERSION] = CURL_SSLVERSION_TLSv1_2;
            // phpcs:enable
        }
        
        return $config;
    }
}
