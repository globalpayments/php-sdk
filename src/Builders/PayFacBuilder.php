<?php
namespace GlobalPayments\Api\Builders;

use GlobalPayments\Api\Entities\Customer;
use GlobalPayments\Api\Entities\Enums\PaymentMethodFunction;
use GlobalPayments\Api\Entities\Enums\StatusChangeReason;
use GlobalPayments\Api\Entities\PayFac\UserReference;
use GlobalPayments\Api\Entities\PaymentStatistics;
use GlobalPayments\Api\Entities\PersonList;
use GlobalPayments\Api\Entities\User;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\PayFac\BankAccountData;
use GlobalPayments\Api\Entities\PayFac\BeneficialOwnerData;
use GlobalPayments\Api\Entities\PayFac\BusinessData;
use GlobalPayments\Api\Entities\PayFac\SignificantOwnerData;
use GlobalPayments\Api\Entities\PayFac\ThreatRiskData;
use GlobalPayments\Api\Entities\PayFac\UserPersonalData;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Exceptions\BuilderException;

/**
 * @property string $userId
 */
class PayFacBuilder extends BaseBuilder
{
    public $transactionType;
    public $transactionModifier;
    public $bankAccountData;
    public $beneficialOwnerData;
    public $businessData;
    public $significantOwnerData;
    public $threatRiskData;
    public $userPersonalData;
    public $creditCardInformation;
    /**
     * @var BankAccountData
     */
    public $achInformation;
    public $secondaryBankInformation;
    public $grossBillingInformation;
    public $accountNumber;
    public $password;
    public $accountPermissions;
    public $negativeLimit;
    public $renewalAccountData;
    public $uploadDocumentData;
    public $singleSignOnData;
    public $amount;
    public $receivingAccountNumber;
    public $allowPending;
    public $ccAmount;
    public $requireCCRefund;
    public $transNum;
    public $flashFundsPaymentCardData;
    public $externalId;
    public $sourceEmail;
    public $deviceDetails;
    /** @var string */
    public $description;
    /**
     * @var array<Product>
     */
    public $productData = [];

    /**
     * @var PersonList
     */
    public $personsData;

    /**
     * @var integer
     */
    public $page;

    /**
     * @var integer
     */
    public $pageSize;

    /**
     * @var PaymentStatistics
     */
    public $paymentStatistics;

    /**
     * @var StatusChangeReason
     */
    public $statusChangeReason;

    /** @var User */
    public $userReference;

    /**
     * @var array
     */
    public $paymentMethodsFunctions;

    /** @var string */
    public $idempotencyKey;

    const UPLOAD_FILE_TYPES = [
        'tif', 'tiff', 'bmp', 'jpg', 'jpeg', 'gif', 'png', 'doc', 'docx'
    ];

    /**
     *
     * {@inheritdoc}
     *
     * @param TransactionType $type
     *            Request transaction type
     *
     * @return
     */
    public function __construct($type)
    {
        parent::__construct($type);
        $this->transactionType = $type;
        $this->transactionModifier = TransactionModifier::NONE;
    }

    /**
     * Executes the builder against the gateway.
     *
     * @return mixed
     */
    public function execute($configName = 'default')
    {
        parent::execute($configName);
        $client = ServicesContainer::instance()->getPayFac($configName);
        switch ($this->transactionModifier)
        {
            case TransactionModifier::MERCHANT:
                return $client->processBoardingUser($this);
            default:
                return $client->processPayFac($this);
        }
    }

    public function __get($name)
    {
        switch ($name) {
            case 'userId':
                if ($this->userReference instanceof UserReference) {
                    return $this->userReference->userId;
                }
                return null;
            default:
                break;
        }
    }

    public function __isset($name)
    {
        return in_array($name, [
                'userId',
            ]) || isset($this->{$name});
    }

    protected function setupValidations()
    {
        $this->validations->of(
            TransactionType::CREATE_ACCOUNT
        )
            ->with(TransactionModifier::NONE)
            ->check('beneficialOwnerData')->isNotNull()
            ->check('businessData')->isNotNull()
            ->check('userPersonalData')->isNotNull()
            ->check('creditCardInformation')->isNotNull();

        $this->validations->of(
            TransactionType::EDIT |
            TransactionType::RESET_PASSWORD |
            TransactionType::RENEW_ACCOUNT |
            TransactionType::UPDATE_OWNERSHIP_DETAILS |
            TransactionType::DEACTIVATE |
            TransactionType::UPLOAD_CHARGEBACK_DOCUMENT |
            TransactionType::OBTAIN_SSO_KEY |
            TransactionType::UPDATE_BANK_ACCOUNT_OWNERSHIP |
            TransactionType::ADD_FUNDS |
            TransactionType::SWEEP_FUNDS |
            TransactionType::ADD_CARD_FLASH_FUNDS |
            TransactionType::PUSH_MONEY_FLASH_FUNDS |
            TransactionType::SPEND_BACK |
            TransactionType::REVERSE_SPLITPAY |
            TransactionType::SPLIT_FUNDS |
            TransactionType::GET_ACCOUNT_BALANCE
        )
            ->with(TransactionModifier::NONE)
            ->check('accountNumber')->isNotNull();

        $this->validations->of(
            TransactionType::UPDATE_OWNERSHIP_DETAILS
        )
            ->with(TransactionModifier::NONE)
            ->check('beneficialOwnerData')->isNotNull();

        $this->validations->of(
            TransactionType::UPLOAD_CHARGEBACK_DOCUMENT
        )
            ->with(TransactionModifier::NONE)
            ->check('uploadDocumentData')->isNotNull();

        $this->validations->of(
            TransactionType::OBTAIN_SSO_KEY
        )
            ->with(TransactionModifier::NONE)
            ->check('singleSignOnData')->isNotNull();

        $this->validations->of(
            TransactionType::UPDATE_BANK_ACCOUNT_OWNERSHIP
        )
            ->with(TransactionModifier::NONE)
            ->check('beneficialOwnerData')->isNotNull();

        $this->validations->of(
            TransactionType::ADD_FUNDS |
                TransactionType::SWEEP_FUNDS |
                TransactionType::PUSH_MONEY_FLASH_FUNDS |
                TransactionType::SPEND_BACK |
                TransactionType::REVERSE_SPLITPAY |
                TransactionType::SPLIT_FUNDS
        )
                ->with(TransactionModifier::NONE)
                ->check('amount')->isNotNull();

        $this->validations->of(TransactionType::ADD_CARD_FLASH_FUNDS)
                ->with(TransactionModifier::NONE)
                ->check('flashFundsPaymentCardData')->isNotNull();

        $this->validations->of(TransactionType::DISBURSE_FUNDS)
                ->with(TransactionModifier::NONE)
                ->check('receivingAccountNumber')->isNotNull();

        $this->validations->of(TransactionType::SPEND_BACK)
                ->with(TransactionModifier::NONE)
                ->check('allowPending')->isNotNull()
                ->check('receivingAccountNumber')->isNotNull();

        $this->validations->of(TransactionType::SPLIT_FUNDS)
                ->with(TransactionModifier::NONE)
                ->check('transNum')->isNotNull()
                ->check('receivingAccountNumber')->isNotNull();

        $this->validations->of(TransactionType::REVERSE_SPLITPAY)
            ->with(TransactionModifier::NONE)
            ->check('transNum')->isNotNull()
            ->check('requireCCRefund')->isNotNull()
            ->check('ccAmount')->isNotNull();

        $this->validations->of(
            TransactionType::FETCH |
            TransactionType::EDIT
        )
            ->with(TransactionModifier::MERCHANT)
            ->check('userId')->isNotNull();
    }

    /*
     * Primary Bank Account Information - Optional. Used to add a bank account to which funds can be settled
     *
     * var Object GlobalPayments\Api\Entities\PayFac\BankAccountData;
     */
    public function withBankAccountData(BankAccountData $bankAccountData, $paymentMethodFunction =  null)
    {
        $this->bankAccountData = $bankAccountData;
        if (!empty($paymentMethodFunction)) {
            $paymentMethodFunction = PaymentMethodFunction::validate($paymentMethodFunction);
            $this->paymentMethodsFunctions[get_class($bankAccountData)] = $paymentMethodFunction;
        }
        return $this;
    }
    /*
     * Merchant Beneficiary Owner Information - Required for all merchants validating KYC based off of personal data
     *
     * var Object GlobalPayments\Api\Entities\PayFac\BeneficialOwnerData;
     */
    public function withBeneficialOwnerData(BeneficialOwnerData $beneficialOwnerData)
    {
        $this->beneficialOwnerData = $beneficialOwnerData;
        return $this;
    }
    /*
     * Business Data - Required for business validated accounts. May also be required for personal validated accounts
     * by ProPay Risk Team
     *
     * var Object GlobalPayments\Api\Entities\PayFac\BusinessData;
     */
    public function withBusinessData(BusinessData $businessData)
    {
        $this->businessData = $businessData;
        return $this;
    }
    /*
     * Significant Owner Information - May be required for some partners based on ProPay Risk decision
     *
     * var Object GlobalPayments\Api\Entities\PayFac\SignificantOwnerData;
     */
    public function withSignificantOwnerData(SignificantOwnerData $significantOwnerData)
    {
        $this->significantOwnerData = $significantOwnerData;
        return $this;
    }
    /*
     * Threat Risk Assessment Information - May be required based on ProPay Risk Decision
     *
     * var Object GlobalPayments\Api\Entities\PayFac\ThreatRiskData;
     */
    public function withThreatRiskData(ThreatRiskData $threatRiskData)
    {
        $this->threatRiskData = $threatRiskData;
        return $this;
    }

    /*
     * User / Merchant Personal Data
     *
     * var Object GlobalPayments\Api\Entities\PayFac\UserPersonalData;
     */
    public function withUserPersonalData(UserPersonalData $userPersonalData)
    {
        $this->userPersonalData = $userPersonalData;
        return $this;
    }
    
    public function withCreditCardData($creditCardInformation, $paymentMethodFunction =  null)
    {
        $this->creditCardInformation = $creditCardInformation;
        if (!empty($paymentMethodFunction)) {
            $paymentMethodFunction = PaymentMethodFunction::validate($paymentMethodFunction);
            $this->paymentMethodsFunctions[get_class($creditCardInformation)] = $paymentMethodFunction;
        }

        return $this;
    }
    
    public function withACHData($achInformation)
    {
        $this->achInformation = $achInformation;
        return $this;
    }
    
    public function withSecondaryBankAccountData($secondaryBankInformation)
    {
        $this->secondaryBankInformation = $secondaryBankInformation;
        return $this;
    }
    
    public function withGrossBillingSettleData($grossBillingInformation)
    {
        $this->grossBillingInformation = $grossBillingInformation;
        return $this;
    }
    
    /*
     * The ProPay account to be updated
     *
     * var int
     */
    public function withAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;
        return $this;
    }
    
    /*
     * Temporary password which will allow a onetime login to ProPay's website. Must be at least eight characters.
     * Must not contain part or the entire first or last name. Must contain at least one capital letter,
     * one lower case letter, and either one symbol or one number
     *
     * var string
     */
    public function withPassword($password)
    {
        $this->password = $password;
        return $this;
    }
    
    public function withAccountPermissions($accountPermissions)
    {
        $this->accountPermissions = $accountPermissions;
        return $this;
    }
    
    /*
     * amount must be greater than zero
     */
    public function withNegativeLimit($negativeLimit)
    {
        $this->negativeLimit = $negativeLimit;
        return $this;
    }
    
    public function withRenewalAccountData($renewalAccountData)
    {
        $this->renewalAccountData = $renewalAccountData;
        return $this;
    }
    
       
    /*
     * Document details
     * 
     * var GlobalPayments\Api\Entities\PayFac\UploadDocumentData
     */
    public function withUploadDocumentData($uploadDocumentData)
    {
        //file validations
        if (!file_exists($uploadDocumentData->documentLocation)) {
            throw new BuilderException('File not found!');
        } elseif (filesize($uploadDocumentData->documentLocation) > 5000000) {
            throw new BuilderException('Max file size 5MB exceeded');
        }
        
        $fileType = pathinfo($uploadDocumentData->documentLocation, PATHINFO_EXTENSION);
        if (!in_array($fileType, self::UPLOAD_FILE_TYPES)) {
            throw new BuilderException('File type is not supported.');
        }
        
        $this->uploadDocumentData = $uploadDocumentData;
        return $this;
    }
    
    public function withSingleSignOnData($singleSignOnData)
    {
        $this->singleSignOnData = $singleSignOnData;
        return $this;
    }
    
    public function withAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }
    
    public function withReceivingAccountNumber($receivingAccountNumber)
    {
        $this->receivingAccountNumber = $receivingAccountNumber;
        return $this;
    }
    
    public function withAllowPending($allowPending)
    {
        $this->allowPending = $allowPending;
        return $this;
    }
    
    public function withCCAmount($ccAmount)
    {
        $this->ccAmount = $ccAmount;
        return $this;
    }
    
    public function withRequireCCRefund($requireCCRefund)
    {
        $this->requireCCRefund = $requireCCRefund;
        return $this;
    }
    
    public function withTransNum($transNum)
    {
        $this->transNum = $transNum;
        return $this;
    }
    
    public function withFlashFundsPaymentCardData($flashFundsPaymentCardData)
    {
        $this->flashFundsPaymentCardData = $flashFundsPaymentCardData;
        return $this;
    }
    
    public function withExternalId($externalId)
    {
        $this->externalId = $externalId;
        return $this;
    }
    
    public function withSourceEmail($sourceEmail)
    {
        $this->sourceEmail = $sourceEmail;
        return $this;
    }
    
    public function withDeviceDetails($deviceDetails)
    {
        $this->deviceDetails = $deviceDetails;
        return $this;
    }

    public function withDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Set the request productData
     *
     * @param array $productData Request productData
     *
     * @return $this
     */
    public function withProductData(array $productData)
    {
        $this->productData = $productData;
        return $this;
    }

    /**
     * Set the request customer Data
     *
     * @param PersonList $personsData Request customer Data
     *
     * @return $this
     */
    public function withPersonsData(PersonList $personsData)
    {
        $this->personsData = $personsData;
        return $this;
    }

    public function withUserReference(UserReference $userReference)
    {
        $this->userReference = $userReference;
        return $this;
    }

    public function withModifier($transactionModifier)
    {
        $this->transactionModifier = $transactionModifier;
        return $this;
    }

    public function withPaymentStatistics($paymentStatistics)
    {
        $this->paymentStatistics = $paymentStatistics;
        return $this;
    }

    public function withStatusChangeReason($statusChangeReason)
    {
        $this->statusChangeReason = $statusChangeReason;
        return $this;
    }

    /**
     * Set the gateway paging criteria for the report
     * @param $page
     * @param $pageSize
     * @return $this
     */
    public function withPaging($page, $pageSize)
    {
        $this->page = $page;
        $this->pageSize = $pageSize;
        return $this;
    }

    public function withIdempotencyKey($value)
    {
        $this->idempotencyKey = $value;

        return $this;
    }
}
