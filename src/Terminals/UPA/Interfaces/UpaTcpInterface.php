<?php

namespace GlobalPayments\Api\Terminals\UPA\Interfaces;

use GlobalPayments\Api\Terminals\Interfaces\IDeviceCommInterface;
use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;
use GlobalPayments\Api\Terminals\UPA\Entities\Enums\UpaMessageId;
use GlobalPayments\Api\Terminals\TerminalUtils;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;

/*
 * TCP interface for the device connection and parse response
 */
class UpaTcpInterface implements IDeviceCommInterface
{
    
    /*
     * TCP fsockopen connection object
     */
    private $tcpConnection = null;
    
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
    
    private $nakCount = 0;
    
    /*
     * @param ConnectionConfig object $config device configuration details
     */
    public function __construct(ConnectionConfig $config)
    {
        $this->deviceDetails = $config;
    }
    
    /*
     * Create socket connection with device
     * Throws GatewayException incase of connection error
     */
    public function connect()
    {
        if (is_resource($this->tcpConnection)) {
            return;
        }
        
        $errno = '';
        $errstr = '';
        
        // open socket
        try {
            $this->tcpConnection = pfsockopen(
                $this->deviceDetails->ipAddress,
                $this->deviceDetails->port,
                $errno,
                $errstr
            );
        } catch (\Exception $e) {
            TerminalUtils::manageLog($this->deviceDetails->logManagementProvider, $errstr, true);
            throw new GatewayException(
                sprintf('Device connection error: %s - %s', $errno, $errstr),
                $errno,
                $errstr
            );
        }
    }
    
    /*
     * Close TCP socket connection with device
     */
    public function disconnect()
    {
        // close socket
        if (is_resource($this->tcpConnection)) {
            fclose($this->tcpConnection);
        }
    }
    
    /*
     * Send request message to device using socket connection
     * @param string $message XML request string
     */
    public function send($message, $requestType = null)
    {
        $this->connect();
        $this->requestType = $requestType;
        $out = '';
                        
        TerminalUtils::manageLog(
            $this->deviceDetails->logManagementProvider,
            "$requestType Request Message\n: $message"
        );
        
        if ($this->tcpConnection !== null) {
            try {
                if (false === ($bytes_written = fwrite($this->tcpConnection, $message))) {
                    throw new GatewayException('Device error: failed to write to socket');
                } else {
                    //set time out for read and write
                    stream_set_timeout($this->tcpConnection, $this->deviceDetails->timeout);
                    ob_implicit_flush(true);
                    $etxCount = 0;
                    do {
                        // read from socket
                        $part = fgets($this->tcpConnection);
                        $out .= $part;
                        
                        if (strpos($part, chr(ControlCodes::ETX)) !== false) {
                            $etxCount++;
                            if ($etxCount === 2) {
                                $requestMessage = [
                                    'message' => 'ACK',
                                    'data' => ""
                                ];
                                $ackMessage = chr(ControlCodes::STX) . chr(ControlCodes::LF);
                                $ackMessage .= json_encode($requestMessage);
                                $ackMessage .= chr(ControlCodes::LF) . chr(ControlCodes::ETX). chr(ControlCodes::LF);
                                fwrite($this->tcpConnection, $ackMessage);
                            } elseif ($etxCount === 3) {
                                break;
                            }
                        }
                    } while (!empty($part) && !feof($this->tcpConnection));
                    ob_implicit_flush(false);
                }
                if (!empty($out)) {
                    TerminalUtils::manageLog(
                        $this->deviceDetails->logManagementProvider,
                        "$requestType Response Message\n: $out"
                    );
                    return $this->parseResponse($out);
                }
            } catch (\Exception $e) {
                throw new GatewayException(
                    'Device error: ' . $e->getMessage(),
                    null,
                    $e->getMessage()
                );
            }
        }
        return;
    }
    
    public function parseResponse($gatewayResponse)
    {
        $responseList = explode(chr(ControlCodes::ETX), $gatewayResponse);
        if (!empty($responseList)) {
            foreach ($responseList as $rawMessage) {
                $message = trim(preg_replace('/[\x00-\x0A\n]/', '', trim($rawMessage)));
                $jsonResponse = json_decode(html_entity_decode($message), 1);
                if ($jsonResponse['message'] == 'MSG') {
                    return $jsonResponse;
                }
            }
        }
        
        return '';
    }
        
    public function __destruct()
    {
        $this->disconnect();
    }
}
