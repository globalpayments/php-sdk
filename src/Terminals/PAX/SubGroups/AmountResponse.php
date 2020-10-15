<?php
namespace GlobalPayments\Api\Terminals\PAX\SubGroups;

use GlobalPayments\Api\Terminals\Interfaces\IResponseSubGroup;
use GlobalPayments\Api\Terminals\TerminalUtils;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;

class AmountResponse implements IResponseSubGroup
{

    public $approvedAmount;

    public $amountDue;

    public $tipAmount;

    public $cashBackAmount;

    public $merchantFee;

    public $taxAmount;

    public $balance1;

    public $balance2;

    public function __construct($messageReader)
    {
        $responseString = $messageReader->readToCode(ControlCodes::FS);
//Split using ControlCodes::US
        $response = preg_split('/[\x1F;]/', $responseString);
        try {
            $responseIndexMapping = [
                'approvedAmount' => 0,
                'amountDue' => 1,
                'tipAmount' => 2,
                'cashBackAmount' => 3,
                'merchantFee' => 4,
                'taxAmount' => 5,
                'balance1' => 6,
                'balance2' => 7
            ];

            foreach ($responseIndexMapping as $property => $index) {
                $this->{$property} = (isset($response[$index]))
                                        ? TerminalUtils::reformatAmount($response[$index])
                                        : '';
            }
        } catch (\Exception $e) {
        }
    }
}
