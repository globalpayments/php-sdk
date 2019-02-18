<?php
namespace GlobalPayments\Api\Entities\OnlineBoarding;

use GlobalPayments\Api\Entities\OnlineBoarding\Enums\BusinessPersonal;
use GlobalPayments\Api\Entities\OnlineBoarding\Enums\CorporationDisregardedEn;
use GlobalPayments\Api\Entities\OnlineBoarding\Enums\PrivatePublic;
use GlobalPayments\Api\Entities\OnlineBoarding\Enums\TypeofOwnershipSelect;
use GlobalPayments\Api\Entities\OnlineBoarding\FormElement;

class BusinessInfo extends FormElement
{
    /**
     * @var PrivatePublic
     */
    public $businessTypeSelect;
    
    /**
     * @var TypeofOwnershipSelect
     */
    public $ownershipTypeSelect;
    
    /**
     * @var CorporationDisregardedEn
     */
    public $irsReportingClassificationForLlcOptionSelect;
    
    /**
     * @var boolean
     */
    public $isFederalIdSignersSsn;
    
    /**
     * @var boolean
     */
    public $dataCompromiseOrComplianceInvestigation;
    
    /**
     * @var Date
     */
    public $dateOfCompromise;
    
    /**
     * @var string
     */
    public $isBusinessPCICompliant;
    
    /**
     * @var string
     */
    public $processOrTransmitThirdPartyCardData;
    
    /**
     * @var string
     */
    public $paymentFacilitatorOrServiceProvider;
    
    /**
     * @var string
     */
    public $homeBasedBusiness;
    
    /**
     * @var string
     */
    public $isSignatureObtainedForReceipt;
    
    /**
     * @var string
     */
    public $contractWithThirdPartyLender;
    
    /**
     * @var string
     */
    public $thirdPartyContractStartDate;
    
    /**
     * @var string
     */
    public $thirdPartyLengthOfContract;
    
    /**
     * @var string
     */
    public $thirdPartyLoanBalance;
    
    /**
     * @var boolean
     */
    public $everFiledBankrupt;
    
    /**
     * @var DateTime
     */
    public $dateOfBankruptcy;
    
    /**
     * @var BusinessPersonal
     */
    public $bankruptcyTypeSelect;
    
    /**
     * @var DateTime
     */
    public $dateBusinessStarted;
    
    /**
     * @var DateTime
     */
    public $dateBusinessAcquired;
    
    /**
     * @var string
     */
    public $percentSalesReturned;
    
    /**
     * @var boolean
     */
    public $acceptCreditCardsOnline;
    
    /**
     * @var string
     */
    public $onlineTransactionsProcessedByHPS;
    
    /**
     * @var string
     */
    public $nameOfPaymentProcessorIfNotHPS;
    
    /**
     * @var boolean
     */
    public $dataStorageOrMerchantServicer;
    
    /**
     * @var string
     */
    public $percentOfBusinessDirectlyWithCustomers;
    
    /**
     * @var string
     */
    public $percentOfBusinessDirectlyWithBusiness;
    
    /**
     * @var string
     */
    public $productsServicesProvided;
    
    /**
     * @var string
     */
    public $useFulfillmentHouse;
    
    /**
     * @var string
     */
    public $refundPolicy;
    
    /**
     * @var string
     */
    public $timeUntilCardIsCharged;
    
    /**
     * @var string
     */
    public $processForAgeRestrictionPurchases;
    
    /**
     * @var boolean
     */
    public $acceptCreditCards;
    
    // hidden fields dependent on AcceptCreditCards
    /**
     * @var DateTime
     */
    public $dateAcceptingCreditCardsStarted;
    
    /**
     * @var string
     */
    public $percentSalesChargebacks;
    
    /**
     * @var string
     */
    public $currentProcessor;
    
    /**
     * @var string
     */
    public $currentMID;
    
    /**
     * @var boolean
     */
    public $seasonalMerchant;
    
    /**
     * @var boolean
     */
    public $operatesInJanuary;
    
    /**
     * @var boolean
     */
    public $operatesInFebruary;
    
    /**
     * @var boolean
     */
    public $operatesInMarch;
    
    /**
     * @var boolean
     */
    public $operatesInApril;
    
    /**
     * @var boolean
     */
    public $operatesInMay;
    
    /**
     * @var boolean
     */
    public $operatesInJune;
    
    /**
     * @var boolean
     */
    public $operatesInJuly;
    
    /**
     * @var boolean
     */
    public $operatesInAugust;
    
    /**
     * @var boolean
     */
    public $operatesInSeptember;
    
    /**
     * @var boolean
     */
    public $operatesInOctober;
    
    /**
     * @var boolean
     */
    public $operatesInNovember;
    
    /**
     * @var boolean
     */
    public $operatesInDecember;

    public function __construct()
    {
        $this->isFederalIdSignersSsn = false;
        $this->dataCompromiseOrComplianceInvestigation = false;
        $this->everFiledBankrupt = false;
        $this->acceptCreditCardsOnline = false;
        $this->dataStorageOrMerchantServicer = false;
        $this->acceptCreditCards = false;
        $this->seasonalMerchant = false;
        $this->operatesInJanuary = false;
        $this->operatesInFebruary = false;
        $this->operatesInMarch = false;
        $this->operatesInApril = false;
        $this->operatesInMay = false;
        $this->operatesInJune = false;
        $this->operatesInJuly = false;
        $this->operatesInAugust = false;
        $this->operatesInSeptember = false;
        $this->operatesInOctober = false;
        $this->operatesInNovember = false;
        $this->operatesInDecember = false;
    }
}
