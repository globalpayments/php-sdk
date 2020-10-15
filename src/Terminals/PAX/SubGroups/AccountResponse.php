<?php
namespace GlobalPayments\Api\Terminals\PAX\SubGroups;

use GlobalPayments\Api\Terminals\Interfaces\IResponseSubGroup;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;
use GlobalPayments\Api\Utils\EnumUtils;
use GlobalPayments\Api\Terminals\PAX\Entities\Enums\PaxEntryMode;
use GlobalPayments\Api\Terminals\Enums\TerminalCardType;

class AccountResponse implements IResponseSubGroup
{

    public $accountNumber;

    public $entryMode;

    public $expireDate;

    public $ebtType;

    public $voucherNumber;

    public $newAccountNumber;

    public $cardType;

    public $cardHolder;

    public $cvdApprovalCode;

    public $cvdMessage;

    public $cardPresent;

    public function __construct($messageReader)
    {
        $responseString = $messageReader->readToCode(ControlCodes::FS);
        // Split using ControlCodes::US
        $response = preg_split('/[\x1F;]/', $responseString);
        try {
            $responseIndexMapping = [
                'accountNumber' => 0,
                'expireDate' => 2,
                'ebtType' => 3,
                'voucherNumber' => 4,
                'newAccountNumber' => 5,
                'cardHolder' => 7,
                'cvdApprovalCode' => 8,
                'cvdMessage' => 9,
                'cardPresent' => 10
            ];

            foreach ($responseIndexMapping as $property => $index) {
                $this->{$property} = (isset($response[$index])) ? $response[$index] : '';
            }

            // entry mode and card Type
            if (isset($response[1])) {
                $entryMode = EnumUtils::parse(new PaxEntryMode(), $response[1]);
                $this->entryMode = str_replace('_', ' ', $entryMode);
            }

            if (isset($response[6])) {
                $cardType = EnumUtils::parse(new TerminalCardType(), $response[6]);
                $this->cardType = str_replace('_', ' ', $cardType);
            }
        } catch (\Exception $e) {
        }
    }
}
