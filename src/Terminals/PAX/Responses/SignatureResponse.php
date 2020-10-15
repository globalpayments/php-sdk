<?php

namespace GlobalPayments\Api\Terminals\PAX\Responses;

use GlobalPayments\Api\Terminals\PAX\Entities\Enums\PaxMessageId;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;
use GlobalPayments\Api\Terminals\Enums\DeviceType;

class SignatureResponse extends PaxDeviceResponse
{

    public $totalLength;
    public $responseLength;
    public $signatureData;
    private $deviceType;
    
    public function __construct($rawResponse, $messageId, $deviceType = DeviceType::PAX_S300)
    {
        $this->deviceType = $deviceType;
        parent::__construct($rawResponse, $messageId);
    }

    public function parseResponse($messageReader)
    {
        parent::parseResponse($messageReader);
        
        if ($this->resultCode == '000000' && $this->command == PaxMessageId::A09_RSP_GET_SIGNATURE) {
            $this->totalLength = $messageReader->readToCode(ControlCodes::FS);
            $this->responseLength = $messageReader->readToCode(ControlCodes::FS);
            $coordinates = $messageReader->readToCode(ControlCodes::FS);
            if (empty($coordinates)) {
                throw new GatewayException(
                    'Device error: ' . $e->getMessage(),
                    null,
                    $e->getMessage()
                );
            }
            $this->signatureData = $this->createSignature($coordinates);
        }
    }
    
    private function createSignature($coordinateString)
    {
        try {
            $width = 150;
            switch ($this->deviceType) {
                case DeviceType::PAX_PX5:
                case DeviceType::PAX_PX7:
                    $width = 350;
                    break;
            }
            
            $coordinates = explode('^', $coordinateString);
            $image = imagecreate($width, 100);
            $backgroundColor = imagecolorallocate($image, 224, 234, 234);
            $textColor = imagecolorallocate($image, 233, 14, 91);

            $index = 1;
            $previousPoints = explode(',', $coordinates[$index]);

            do {
                $coordinate = $coordinates[$index++];
                $startPoints = explode(',', $coordinate);
                //draw line
                if (isset($previousPoints[0]) && $previousPoints[1] && $startPoints[0] && $startPoints[1]) {
                    imageline(
                        $image,
                        $previousPoints[0],
                        $previousPoints[1],
                        $startPoints[0],
                        $startPoints[1],
                        $textColor
                    );
                }
                $previousPoints = $startPoints;
            } while ($coordinates[$index] != '~');

            // start buffering
            ob_start();
            imagepng($image);
            $contents = ob_get_contents();
            ob_end_clean();

            return base64_encode($contents);
        } catch (\Exception $e) {
            return "Error occurred while getting signature: " . $e->getMessage();
        }
    }
}
