<?php
namespace GlobalPayments\Api\Terminals\PAX\SubGroups;

use GlobalPayments\Api\Terminals\Abstractions\IResponseSubGroup;
use GlobalPayments\Api\Terminals\TerminalUtils;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;

class AmountResponse implements IResponseSubGroup
{

    public float|int|string|null $approvedAmount = null;

    public float|int|string|null $amountDue = null;

    public float|int|string|null $tipAmount = null;

    public float|int|string|null $cashBackAmount = null;

    public float|int|string|null $merchantFee = null;

    public float|int|string|null $taxAmount = null;

    public float|int|string|null $balance1 = null;

    public float|int|string|null $balance2 = null;

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
                $this->{$property} = (isset($response[$index]) && !empty($response[$index]))
                                        ? TerminalUtils::reformatAmount($response[$index])
                                        : '';
            }
        } catch (\Exception $e) {
        }
    }
}
