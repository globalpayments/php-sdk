<?php

namespace GlobalPayments\Api\Terminals\PAX\Interfaces;

use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Terminals\{ConnectionConfig, TerminalUtils};
use GlobalPayments\Api\Terminals\Enums\{ControlCodes, ConnectionModes};
use GlobalPayments\Api\Terminals\Interfaces\IDeviceCommInterface;

/*
 * TCP interface for the device connection and parse response
 */
class PaxTcpInterface implements IDeviceCommInterface
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
            if ($this->deviceDetails->connectionMode === ConnectionModes::SSL_TCP) {
                // Define the constant manually for earlier versions of PHP.
                // Disable phpcs here since this constant does not exist until PHP 5.5.
                // phpcs:disable
                if (!defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
                    define('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT', 33);
                }
                $context = stream_context_create([
                    'ssl' => [
                        "crypto_method" => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT
                    ]
                ]);
                // phpcs:enable
                
                stream_context_set_option($context, 'ssl', 'allow_self_signed', true);
                stream_context_set_option($context, 'ssl', 'verify_peer', false); //true
                stream_context_set_option($context, 'ssl', 'verify_peer_name', false); //true
                
                $this->tcpConnection = stream_socket_client(
                    $this->deviceDetails->ipAddress . ':' . $this->deviceDetails->port,
                    $errno,
                    $errstr,
                    $this->deviceDetails->timeout,
                    STREAM_CLIENT_CONNECT,
                    $context
                );
            } else {
                $this->tcpConnection = pfsockopen(
                    $this->deviceDetails->ipAddress,
                    $this->deviceDetails->port,
                    $errno,
                    $errstr
                );
            }
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
        if ($this->tcpConnection !== null) {
            try {
                TerminalUtils::manageLog($this->deviceDetails->logManagementProvider, "Input Message: $message");
                for ($i=0; $i < 3; $i++) {
                    fputs($this->tcpConnection, $message);
                    $bytesReceived = $this->getTerminalResponseAsync();
                    
                    if ($bytesReceived !== null) {
                        $length = strlen($bytesReceived);
                        
                        //last chr is the LRC
                        $lrc = isset($bytesReceived[$length - 1]) ? $bytesReceived[$length - 1] : '';
                        
                        //remove first and last chr to caluclate LRC
                        $rawString = isset($bytesReceived[$length - 1]) ? substr($bytesReceived, 0, ($length - 1)) : '';
                        $calculateLRC = TerminalUtils::calculateLRC(trim($rawString));
                        
                        if (empty($lrc) || $lrc != $calculateLRC) {
                            $this->sendControlCode(ControlCodes::NAK);
                        } else {
                            TerminalUtils::manageLog(
                                $this->deviceDetails->logManagementProvider,
                                "Device Response : $rawString "
                            );
                            $this->sendControlCode(ControlCodes::ACK);
                            $this->disconnect();
                            return $rawString;
                        }
                    }
                }
            } catch (\Exception $e) {
                TerminalUtils::manageLog($this->deviceDetails->logManagementProvider, $e->getMessage(), true);
                throw new GatewayException(
                    'Device error: ' . $e->getMessage(),
                    null,
                    $e->getMessage()
                );
            }
        }
        return;
    }
    
    /*
     * Filter the device response. remove control characters
     *
     *
     * @param String $gatewayResponse response from device
     */
    public function parseResponse($gatewayRawResponse)
    {
    }
    
    
    private function getTerminalResponse()
    {
        $bytesReceived = $this->awaitResponse(true);
        
        if (!empty($bytesReceived)) {
            $code = bin2hex($bytesReceived[0]);
            
            if ($code == ControlCodes::NAK) {
                return null;
            } elseif ($code == ControlCodes::EOT) {
                throw new GatewayException('Terminal returned EOT for the current message');
            } elseif ($code == ControlCodes::ACK) {
                return $this->getTerminalResponse();
            } elseif ($code == ControlCodes::STX) {
                return $bytesReceived;
            } else {
                throw new GatewayException("Unknown message received: $code");
            }
        }
    }
    
    private function awaitResponse($readString = false)
    {
        $startTime = time();
        do {
            $part = ($readString === true) ? fgets($this->tcpConnection) : fgetc($this->tcpConnection);
            if (!empty($part)) {
                if ($readString) {
                    return substr($part, 0, strpos($part, chr(0x03)) + 2);
                }
                return $part;
            }
            $timeDiff = time() - $startTime;
            if ($timeDiff >= $this->deviceDetails->timeout) {
                break;
            }
        } while (true);
        
        throw new GatewayException(
            'Terminal did not respond in the given timeout'
        );
    }
    
    private function sendControlCode($code)
    {
        try {
            if ($code != ControlCodes::NAK) {
                $code = bin2hex($code);
                $this->nakCount = 0;
                fputs($this->tcpConnection, $code);
            } elseif (++$this->nakCount == 3) {
                $this->sendControlCode(ControlCodes::EOT);
            }
        } catch (\Exception $e) {
            throw new GatewayException("Failed to send control code.");
        }
    }
    
    private function getTerminalResponseAsync()
    {
        $bytesReceived = $this->awaitResponse();
        
        if (!empty($bytesReceived)) {
            $code = bin2hex($bytesReceived);
            
            if ($code == ControlCodes::NAK) {
                return null;
            } elseif ($code == ControlCodes::EOT) {
                throw new GatewayException('Terminal returned EOT for the current message');
            } elseif ($code == ControlCodes::ACK) {
                return $this->getTerminalResponse();
            } elseif ($code == ControlCodes::STX) {
                return $bytesReceived;
            } else {
                throw new GatewayException("Unknown message received: $code");
            }
        }
    }
    
    public function __destruct()
    {
        $this->disconnect();
    }
}
