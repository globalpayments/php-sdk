<?php

namespace GlobalPayments\Api\Entities;

class AutoSubstantiation
{
    public $amounts;

    public function getClinicSubTotal()
    {
        return $this->amounts['SUBTOTAL_CLINIC_OR_OTHER_AMT'];
    }

    public function setClinicSubTotal($value)
    {
        $this->amounts['SUBTOTAL_CLINIC_OR_OTHER_AMT'] = $value;
        $this->amounts['TOTAL_HEALTHCARE_AMT'] += $value;
    }

    public function getCopaySubTotal()
    {
        return $this->amounts['SUBTOTAL_COPAY_AMT'];
    }

    public function setCopaySubTotal($value)
    {
        $this->amounts['SUBTOTAL_COPAY_AMT'] = $value;
        $this->amounts['TOTAL_HEALTHCARE_AMT'] += $value;
    }

    public function getDentalSubTotal()
    {
        return $this->amounts['SUBTOTAL_DENTAL_AMT'];
    }

    public function setDentalSubTotal($value)
    {
        $this->amounts['SUBTOTAL_DENTAL_AMT'] = $value;
        $this->amounts['TOTAL_HEALTHCARE_AMT'] += $value;
    }

    public $merchantVerificationValue;

    public function getPrescriptionSubTotal()
    {
        return $this->amounts['SUBTOTAL_PRESCRIPTION_AMT'];
    }

    public function setPrescriptionSubTotal($value)
    {
        $this->amounts['SUBTOTAL_PRESCRIPTION_AMT'] = $value;
        $this->amounts['TOTAL_HEALTHCARE_AMT'] += $value;
    }

    /**
     * Indicates if real time substantiation was used
     *
     * @var bool
     */
    public $realTimeSubstantiation;

    public function getTotalHealthcareAmount()
    {
        return $this->amounts['TOTAL_HEALTHCARE_AMT'];
    }

    public function getVisionSubTotal()
    {
        return $this->amounts['SUBTOTAL_VISION__OPTICAL_AMT'];
    }

    public function setVisionSubTotal($value)
    {
        $this->amounts['SUBTOTAL_VISION__OPTICAL_AMT'] = $value;
        $this->amounts['TOTAL_HEALTHCARE_AMT'] += $value;
    }

    public function __construct()
    {
        $this->amounts['TOTAL_HEALTHCARE_AMT'] = 0;
        $this->amounts['SUBTOTAL_PRESCRIPTION_AMT'] = 0;
        $this->amounts['SUBTOTAL_VISION__OPTICAL_AMT'] = 0;
        $this->amounts['SUBTOTAL_CLINIC_OR_OTHER_AMT'] = 0;
        $this->amounts['SUBTOTAL_DENTAL_AMT'] = 0;
        $this->amounts['SUBTOTAL_COPAY_AMT'] = 0;
    }
}
