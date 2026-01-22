<?php
namespace GlobalPayments\Api\Terminals\PAX\SubGroups;

use GlobalPayments\Api\Terminals\Abstractions\IResponseSubGroup;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;

class CheckResponse implements IResponseSubGroup
{

    public ?string $saleType = null;

    public ?string $routingNumber = null;

    public ?string $accountNumber = null;

    public ?string $checkNumber = null;

    public ?string $checkType = null;

    public ?string $idType = null;

    public ?string $idValue = null;

    public ?string $DOB = null;

    public ?string $phoneNumber = null;

    public ?string $zipCode = null;

    public function __construct($messageReader)
    {
        $responseString = $messageReader->readToCode(ControlCodes::FS);
        // Split using ControlCodes::US
        $response = preg_split('/[\x1F;]/', $responseString);
        try {
            $responseIndexMapping = [
                'saleType' => 0,
                'routingNumber' => 1,
                'accountNumber' => 2,
                'checkNumber' => 3,
                'checkType' => 4,
                'idType' => 5,
                'idValue' => 6,
                'DOB' => 7,
                'phoneNumber' => 8,
                'zipCode' => 9
            ];

            foreach ($responseIndexMapping as $property => $index) {
                $this->{$property} = (isset($response[$index])) ? $response[$index] : '';
            }
        } catch (\Exception $e) {
        }
    }
}
