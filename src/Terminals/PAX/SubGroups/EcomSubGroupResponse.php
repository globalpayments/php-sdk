<?php
namespace GlobalPayments\Api\Terminals\PAX\SubGroups;

use GlobalPayments\Api\Terminals\Interfaces\IResponseSubGroup;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;

class EcomSubGroupResponse implements IResponseSubGroup
{

    public $ecomMode;

    public $transactionType;

    public $secureType;

    public $orderNumber;

    public $installments;

    public $currentInstallment;

    public function __construct($messageReader)
    {
        $responseString = $messageReader->readToCode(ControlCodes::FS);
        $response = preg_split('/[\x1F;]/', $responseString);
        try {
            $responseIndexMapping = [
                'ecomMode' => 0,
                'transactionType' => 1,
                'secureType' => 2,
                'orderNumber' => 3,
                'installments' => 4,
                'currentInstallment' => 5
            ];

            foreach ($responseIndexMapping as $property => $index) {
                $this->{$property} = (isset($response[$index])) ? $response[$index] : '';
            }
        } catch (\Exception $e) {
        }
    }
}
