<?php
namespace GlobalPayments\Api\Terminals\PAX\SubGroups;

use GlobalPayments\Api\Terminals\Interfaces\IResponseSubGroup;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;

class HostResponse implements IResponseSubGroup
{

    public $hostResponseCode;

    public $hostResponseMessage;

    public $authCode;

    public $hostReferenceNumber;

    public $traceNumber;

    public $batchNumber;

    public function __construct($messageReader)
    {
        $responseString = $messageReader->readToCode(ControlCodes::FS);
        // Split using ControlCodes::US
        $response = preg_split('/[\x1F;]/', $responseString);
        try {
            $responseIndexMapping = [
                'hostResponseCode' => 0,
                'hostResponseMessage' => 1,
                'authCode' => 2,
                'hostReferenceNumber' => 3,
                'traceNumber' => 4,
                'batchNumber' => 5
            ];

            foreach ($responseIndexMapping as $property => $index) {
                $this->{$property} = (isset($response[$index])) ? $response[$index] : '';
            }
        } catch (\Exception $e) {
        }
    }
}
