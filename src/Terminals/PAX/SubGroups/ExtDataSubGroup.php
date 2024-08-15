<?php

namespace GlobalPayments\Api\Terminals\PAX\SubGroups;

use GlobalPayments\Api\Entities\AutoSubstantiation;
use GlobalPayments\Api\Terminals\Abstractions\IRequestSubGroup;
use GlobalPayments\Api\Terminals\Enums\ControlCodes;

class ExtDataSubGroup implements IRequestSubGroup
{
    /**
     * 
     * @var array
     */
    public array $details;

    /**
     * 
     * @return string 
     */
    public function getElementString(): string
    {
        $message = '';
        if (!empty($this->details)) {
            foreach ($this->details as $key => $val) {
                if (is_a($val, 'GlobalPayments\Api\Entities\AutoSubstantiation')) {
                    $message .= sprintf(
                        "%s=%s",
                        $key,
                        $this->autoSubHelper($val)
                    );
                    continue;
                }
                $message .= sprintf("%s=%s%s", $key, $val, chr(ControlCodes::US));
            }
        }
        return rtrim($message, chr(ControlCodes::US));
    }

    /**
     * 
     * @param AutoSubstantiation $info 
     * @return string 
     */
    private function autoSubHelper(AutoSubstantiation $info): string
    {
        $string = sprintf("%s%s", 'FSA', chr(ControlCodes::COLON));
        $string .= sprintf(
            "%s%s%s|",
            'HealthCare',
            chr(ControlCodes::COMMA),
            $info->amounts["TOTAL_HEALTHCARE_AMT"] * 100
        );

        if ($info->amounts["SUBTOTAL_PRESCRIPTION_AMT"] > 0) {
            $string .= sprintf(
                "%s%s%s|",
                'Rx',
                chr(ControlCodes::COMMA),
                $info->amounts["SUBTOTAL_PRESCRIPTION_AMT"] * 100
            );
        }

        if ($info->amounts["SUBTOTAL_VISION__OPTICAL_AMT"] > 0) {
            $string .= sprintf(
                "%s%s%s|",
                'Vision',
                chr(ControlCodes::COMMA),
                $info->amounts["SUBTOTAL_VISION__OPTICAL_AMT"] * 100
            );
        }

        if ($info->amounts["SUBTOTAL_DENTAL_AMT"] > 0) {
            $string .= sprintf(
                "%s%s%s|",
                'Dental',
                chr(ControlCodes::COMMA),
                $info->amounts["SUBTOTAL_DENTAL_AMT"] * 100
            );
        }

        if ($info->amounts["SUBTOTAL_CLINIC_OR_OTHER_AMT"] > 0) {
            $string .= sprintf(
                "%s%s%s|",
                'Clinical',
                chr(ControlCodes::COMMA),
                $info->amounts["SUBTOTAL_CLINIC_OR_OTHER_AMT"] * 100
            );
        }

        if ($info->amounts["SUBTOTAL_COPAY_AMT"] > 0) {
            $string .= sprintf(
                "%s%s%s|",
                'CoPay',
                chr(ControlCodes::COMMA),
                $info->amounts["SUBTOTAL_COPAY_AMT"] * 100
            );
        }

        return $string;
    }
}
