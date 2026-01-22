<?php
namespace GlobalPayments\Api\Terminals\PAX\SubGroups;

use GlobalPayments\Api\Terminals\Abstractions\IResponseSubGroup;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;

class HostResponse implements IResponseSubGroup
{

    public ?string $hostResponseCode = null;

    public ?string $hostResponseMessage = null;

    public ?string $authCode = null;

    public ?string $hostReferenceNumber = null;

    public ?string $traceNumber = null;

    public ?string $batchNumber = null;

    public ?string $cardBrandTransactionId = null;

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
                'batchNumber' => 5,
                'cardBrandTransactionId' => 6,
            ];

            foreach ($responseIndexMapping as $property => $index) {
                $this->{$property} = (isset($response[$index])) ? $response[$index] : '';
            }
        } catch (\Exception $e) {
        }
    }
}
