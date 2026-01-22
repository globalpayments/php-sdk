<?php
namespace GlobalPayments\Api\Terminals\PAX\SubGroups;

use GlobalPayments\Api\Terminals\Abstractions\IResponseSubGroup;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;

class EcomSubGroupResponse implements IResponseSubGroup
{

    public ?string $ecomMode = null;

    public ?string $transactionType = null;

    public ?string $secureType = null;

    public ?string $orderNumber = null;

    public string|int|null $installments = null;

    public string|int|null $currentInstallment = null;

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
