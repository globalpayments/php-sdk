<?php

namespace GlobalPayments\Api\Terminals\PAX\Responses;

use GlobalPayments\Api\Terminals\PAX\Entities\PaxResponse;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Utils\MessageReader;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;

class PaxBaseResponse extends PaxResponse
{
    public $messageId;

    public function __construct($rawResponse, $messageId)
    {
        $this->messageId = $messageId;
        $this->parseResponse(new MessageReader($rawResponse));
    }

    public function parseResponse($messageReader)
    {
        $code = $messageReader->readCode();
        $this->status = $messageReader->readToCode(ControlCodes::FS);
        $this->command = $messageReader->readToCode(ControlCodes::FS);
        $this->versionNumber = $messageReader->readToCode(ControlCodes::FS);
        $this->deviceResponseCode = $messageReader->readToCode(ControlCodes::FS);
        $this->deviceResponseText = $messageReader->readToCode(ControlCodes::FS);
        $this->checkResponse();
    }

    /*
     * Check the device response code
     *
     * @param PaxResponse $gatewayResponse parsed response from device
     * @param array       $acceptedCodes list of success response codes
     *
     * @return raise GatewayException incase of different unexpected code
     */

    public function checkResponse($acceptedCodes = null)
    {
        if ($acceptedCodes === null) {
            $acceptedCodes = ["000000"];
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
