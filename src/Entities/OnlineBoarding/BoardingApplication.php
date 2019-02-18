<?php
namespace GlobalPayments\Api\Entities\OnlineBoarding;

use GlobalPayments\Api\Utils\MultipartForm;
use GlobalPayments\Api\Entities\Enum;

class BoardingApplication
{
    /**
     * @var BankingInfo
     */
    public $bankingInfo;
    
    /**
     * @var MerchantInfo
     */
    public $merchantInfo;
    
    /**
     * @var LegalInfo
     */
    public $legalInfo;
    
    /**
     * @var boolean
     */
    public $legalInfoSameAsMerchant;
    
    /**
     * @var Headquarters
     */
    public $headquarters;
    
    /**
     * @var BusinessInfo
     */
    public $businessInfo;
    
    /**
     * @var SaleMethods
     */
    public $salesMethods;
    
    /**
     * @var ProcessingMethod
     */
    public $processingMethod;
    
    /**
     * @var FutureDeliveryInfo
     */
    public $futureDeliveryInfo;
    
    /**
     * @var GolfIndustry
     */
    public $golfIndustry;
    
    /**
     * @var SalonIndustry
     */
    public $salonIndustry;
    
    /**
     * @var LodgingResportIndustry
     */
    public $lodgingResortInfo;
    
    /**
     * @var TransactionInfo
     */
    public $transactionInfo;
    
    /**
     * @var EquipmentInfo
     */
    public $equipmentInfo;
    
    /**
     * @var ShippingOptions
     */
    public $shippingOptions;
    
    /**
     * @var DepositOptions
     */
    public $depositOptions;
    
    /**
     * @var StatementOptions
     */
    public $statementOptions;
    
    /**
     * @var DisputeOptions
     */
    public $disputeOptions;
    
    /**
     * @var IEnumerable<OwnerOfficer>
     */
    public $ownerOfficers = [];

    public function buildForm()
    {
        $form = new MultipartForm(false);
        $this->populateForm($form, $this->merchantInfo);

        if ($this->legalInfoSameAsMerchant) {
            $this->legalInfo = new LegalInfo();
            $this->legalInfo->corporateName = $this->merchantInfo->merchantDbaName;
            $this->legalInfo->corporateStreet = $this->merchantInfo->merchantStreet;
            $this->legalInfo->corporateCity = $this->merchantInfo->merchantCity;
            $this->legalInfo->corporatePhone = $this->merchantInfo->merchantPhone;
            $this->legalInfo->corporateStatesSelect = $this->merchantInfo->merchantStatesSelect;
            $this->legalInfo->corporateZip = $this->merchantInfo->merchantZip;
        }

        $this->populateForm($form, $this->legalInfo);
        $this->populateForm($form, $this->businessInfo);

        // OPTIONALS
        if (!empty($this->headquarters)) {
            $this->headquarters->populateForm($form);
        }
        if (!empty($this->salesMethods)) {
            $this->salesMethods->populateForm($form);
        }
        if (!empty($this->processingMethod)) {
            $this->processingMethod->populateForm($form);
        }
        if (!empty($this->futureDeliveryInfo)) {
            $this->futureDeliveryInfo->populateForm($form);
        }
        if (!empty($this->golfIndustry)) {
            $this->golfIndustry->populateForm($form);
        }
        if (!empty($this->salonIndustry)) {
            $this->salonIndustry->populateForm($form);
        }
        if (!empty($this->lodgingResortInfo)) {
            $this->lodgingResortInfo->populateForm($form);
        }
        if (!empty($this->transactionInfo)) {
            $this->transactionInfo->populateForm($form);
        }
        if (!empty($this->equipmentInfo)) {
            $this->equipmentInfo->populateForm($form);
        }
        if (!empty($this->shippingOptions)) {
            $this->shippingOptions->populateForm($form);
        }
        if (!empty($this->depositOptions)) {
            $this->depositOptions->populateForm($form);
        }
        if (!empty($this->statementOptions)) {
            $this->statementOptions->populateForm($form);
        }
        if (!empty($this->disputeOptions)) {
            $this->disputeOptions->populateForm($form);
        }

        // owners/officers
        for ($i = 0; $i < 10; $i++) {
            $owner = new OwnerOfficer();
            if ($i < count($this->ownerOfficers)) {
                $owner = $this->ownerOfficers[$i];
            }

            $owner->prefix = sprintf('OwnerOfficer%s_', $i + 1);
            $this->populateForm($form, $owner);

            //signers
            $form->set(sprintf('Signer%sEmail', $i + 1), $owner->emailAddress ?? ' ');
            $form->set(sprintf('Signer%sFullName', $i + 1), $owner->fullName ?? ' ');
        }

        // banking info
        $this->populateForm($form, $this->bankingInfo);
        if (!empty($this->bankingInfo)) {
            foreach ($this->bankingInfo->bankAccounts as $account) {
                $account->prefix = sprintf(
                    'MerchantAccount%s_',
                    array_search($account, $this->bankingInfo->bankAccounts) + 1
                );
                $account->populateForm($form);
            }
        }
        return $form;
    }

    public function processValidationResult(string $json)
    {
        $response = json_decode($json, true);
        $validationErrors = [];

        return $validationErrors;

        $this->merchantInfo->processValidation($response, $validationErrors);
        $this->legalInfo->processValidation($response, $validationErrors);
        $this->businessInfo->processValidation($response, $validationErrors);

        // OPTIONALS
        if (!empty($this->headquarters)) {
            $this->headquarters->processValidation($response, $validationErrors);
        }
        if (!empty($this->salesMethods)) {
            $this->salesMethods->processValidation($response, $validationErrors);
        }
        if (!empty($this->processingMethod)) {
            $this->processingMethod->processValidation($response, $validationErrors);
        }
        if (!empty($this->futureDeliveryInfo)) {
            $this->futureDeliveryInfo->processValidation($response, $validationErrors);
        }
        if (!empty($this->golfIndustry)) {
            $this->golfIndustry->processValidation($response, $validationErrors);
        }
        if (!empty($this->salonIndustry)) {
            $this->salonIndustry->processValidation($response, $validationErrors);
        }
        if (!empty($this->lodgingResortInfo)) {
            $this->lodgingResortInfo->processValidation($response, $validationErrors);
        }
        if (!empty($this->transactionInfo)) {
            $this->transactionInfo->processValidation($response, $validationErrors);
        }
        if (!empty($this->equipmentInfo)) {
            $this->equipmentInfo->processValidation($response, $validationErrors);
        }
        if (!empty($this->shippingOptions)) {
            $this->shippingOptions->processValidation($response, $validationErrors);
        }
        if (!empty($this->depositOptions)) {
            $this->depositOptions->processValidation($response, $validationErrors);
        }
        if (!empty($this->statementOptions)) {
            $this->statementOptions->processValidation($response, $validationErrors);
        }
        if (!empty($this->disputeOptions)) {
            $this->disputeOptions->processValidation($response, $validationErrors);
        }

        // owners/officers
        for ($i = 0; $i < 10; $i++) {
            if ($i < count($this->ownerOfficers)) {
                $owner = $this->ownerOfficers[$i];
                $owner->prefix = sprintf('OwnerOfficer%s_', $i + 1);
                $owner->processValidation($response, $validationErrors);
            }
        }

        // banking info
        $this->bankingInfo->processValidation($response, $validationErrors);
        if (!empty($this->bankingInfo)) {
            foreach ($this->bankingInfo->bankAccounts as $account) {
                $account->prefix = sprintf(
                    'MerchantAccount%s_',
                    array_search($account, $this->bankingInfo->bankAccounts) + 1
                );
                $account->processValidation($response, $validationErrors);
            }
        }

        return $validationErrors;
    }

    private function populateForm(MultipartForm &$form, FormElement $element)
    {
        if (empty($element)) {
            $element = new FormElement();
        }
        $element->populateForm($form);
    }
}
