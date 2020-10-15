<?php
namespace GlobalPayments\Api\Terminals\PAX\SubGroups;

use GlobalPayments\Api\Terminals\Interfaces\IResponseSubGroup;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;

class CheckResponse implements IResponseSubGroup
{

    public $saleType;

    public $routingNumber;

    public $accountNumber;

    public $checkNumber;

    public $checkType;

    public $idType;

    public $idValue;

    public $DOB;

    public $phoneNumber;

    public $zipCode;

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
