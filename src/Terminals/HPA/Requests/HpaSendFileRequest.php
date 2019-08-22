<?php

namespace GlobalPayments\Api\Terminals\HPA\Requests;

use GlobalPayments\Api\Terminals\Enums\DeviceType;
use GlobalPayments\Api\Terminals\HPA\Entities\Enums\HpaSendFileType;
use GlobalPayments\Api\Entities\Exceptions\BuilderException;

class HpaSendFileRequest
{

    public $deviceConfig;

    public function __construct($deviceConfig)
    {
        $this->deviceConfig = $deviceConfig;
    }

    public function validate($sendFileInfo)
    {
        if (empty($sendFileInfo) || empty($sendFileInfo->imageLocation) ||
                empty($sendFileInfo->imageType)) {
            throw new BuilderException("Input error: Image location / type missing");
        }

        //validate file height and width
        list($width, $height) = getimagesize($sendFileInfo->imageLocation);

        //validate image size
        if ($sendFileInfo->imageType == HpaSendFileType::BANNER) {
            if ($this->deviceConfig->deviceType == DeviceType::HPA_ISC250 &&
                    ($height > 60 || $width > 480)) {
                throw new BuilderException("Incorrect file height and width");
            } elseif ($this->deviceConfig->deviceType == DeviceType::HPA_IPP350 &&
                    ($height > 40 || $width > 320)) {
                throw new BuilderException("Incorrect file height and width");
            }
        } elseif ($sendFileInfo->imageType == HpaSendFileType::IDLELOGO) {
            if ($this->deviceConfig->deviceType == DeviceType::HPA_ISC250 &&
                    ($height > 272 || $width > 480)) {
                throw new BuilderException("Incorrect file height and width");
            } elseif ($this->deviceConfig->deviceType == DeviceType::HPA_IPP350 &&
                    ($height > 240 || $width > 320)) {
                throw new BuilderException("Incorrect file height and width");
            }
        }
    }

    public function getFileInformation($sendFileInfo)
    {
        try {
            //convert image to hexa decimal ASCII format
            $hex = unpack("H*", file_get_contents($sendFileInfo->imageLocation));
            $hex = current($hex);

            $fileInfo['fileSize'] = filesize($sendFileInfo->imageLocation);
            $fileInfo['fileData'] = $hex;
            $fileInfo['fileDataSize'] = strlen($hex);

            return $fileInfo;
        } catch (Exception $e) {
            throw new BuilderException("Input error: " . $e->getMessage);
        }
    }
}
