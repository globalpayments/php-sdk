<?php

namespace GlobalPayments\Api\Terminals\PAX\Responses;

use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Terminals\TerminalResponse;
use GlobalPayments\Api\Utils\MessageReader;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;

class PaxBaseResponse extends TerminalResponse
{
    public $messageId;

    public function __construct($rawResponse, $messageId)
    {
        $this->messageId = $messageId;
        $this->parseResponse(new MessageReader($rawResponse));
    }

    public function parseResponse($messageReader)
    {
        $this->status = $messageReader->readToCode(ControlCodes::FS);
        $this->command = $messageReader->readToCode(ControlCodes::FS);
        $this->version = $messageReader->readToCode(ControlCodes::FS);
        $this->deviceResponseCode = $messageReader->readToCode(ControlCodes::FS);
        $this->deviceResponseText = $messageReader->readToCode(ControlCodes::FS);
        $this->checkResponse();
    }

    /*
     * Check the device response code
     *
     * @param DeviceResponse $gatewayResponse parsed response from device
     * @param array       $acceptedCodes list of success response codes
     *
     * @return raise GatewayException incase of different unexpected code
     */

    public function checkResponse($acceptedCodes = null)
    {
        if ($acceptedCodes === null) {
            $acceptedCodes = ["000000", "000100", "000002"];
        }

        if (!empty($this->deviceResponseText)) {
            $responseCode = (string) $this->deviceResponseCode;
            $responseMessage = (string) $this->deviceResponseText;

            if (!in_array($responseCode, $acceptedCodes)) {
                throw new GatewayException(
                    sprintf(
                        'Unexpected Gateway Response: %s - %s',
                        $responseCode,
                        $responseMessage
                    ),
                    $responseCode,
                    $responseMessage
                );
            }
        } else {
            throw new GatewayException('Invalid Gateway Response');
        }
    }
}
