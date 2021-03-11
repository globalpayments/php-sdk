<?php

namespace GlobalPayments\Api\Gateways;

use DOMDocument;
use GlobalPayments\Api\Builders\PayFacBuilder;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException;
use GlobalPayments\Api\Entities\PayFac\AccountPermissions;
use GlobalPayments\Api\Entities\PayFac\BusinessData;
use GlobalPayments\Api\Entities\PayFac\FlashFundsPaymentCardData;
use GlobalPayments\Api\Entities\PayFac\UploadDocumentData;
use GlobalPayments\Api\Entities\PayFac\UserPersonalData;
use GlobalPayments\Api\Entities\PayFac\PayFacResponseData;
use GlobalPayments\Api\Entities\PayFac\RenewAccountData;
use GlobalPayments\Api\Entities\PayFac\SingleSignOnData;
use GlobalPayments\Api\Entities\PayFac\OwnerDetailsResponseData;

class ProPayConnector extends XmlGateway implements IPayFacProvider
{
    public $certStr;
    public $termId;
    public $selfSignedCert;
    
    public function processPayFac(PayFacBuilder $builder)
    {
        $this->updateGatewaySettings($builder);
        
        $xml = new DOMDocument();
        $transaction = $xml->createElement('XMLRequest');
        
        // Credentials
        $transaction->appendChild($xml->createElement('certStr', $this->certStr));
        $transaction->appendChild($xml->createElement('termid', $this->termId));
        $transaction->appendChild($xml->createElement('class', 'partner'));
        
        // Transaction
        $xmlTrans = $xml->createElement('XMLTrans');
        $xmlTrans->appendChild($xml->createElement('transType', $this->mapRequestType($builder)));
        
        $this->hydrateCommonFields($xml, $xmlTrans, $builder);
        
        if ($builder->transactionType === TransactionType::CREATE_ACCOUNT) {
            $this->hydrateAccountDetails($xml, $xmlTrans, $builder);
        } elseif ($builder->transactionType === TransactionType::EDIT) {
            $this->hydrateAccountEditDetails($xml, $xmlTrans, $builder);
        } elseif ($builder->transactionType === TransactionType::RENEW_ACCOUNT && !empty($builder->renewalAccountData)) {
            $this->hydrateAccountRenewDetails($xml, $xmlTrans, $builder->renewalAccountData);
        } elseif ($builder->transactionType === TransactionType::UPDATE_OWNERSHIP_DETAILS
                && !empty($builder->beneficialOwnerData)) {
            $this->hydrateBeneficialOwnerData($xml, $xmlTrans, $builder->beneficialOwnerData);
        } elseif (!empty($builder->uploadDocumentData)) {
            $this->hydrateUploadDocument($xml, $xmlTrans, $builder->uploadDocumentData);
        } elseif (!empty($builder->singleSignOnData)) {
            $this->hydrateSingleSignOnData($xml, $xmlTrans, $builder->singleSignOnData);
        } elseif ($builder->transactionType === TransactionType::UPDATE_BANK_ACCOUNT_OWNERSHIP
            && !empty($builder->beneficialOwnerData)) {
                $this->updateBankAccountOwnershipInfo($xml, $xmlTrans, $builder->beneficialOwnerData);
        } elseif (!empty($builder->flashFundsPaymentCardData)) {
            $this->hydrateFlashFundsData($xml, $xmlTrans, $builder->flashFundsPaymentCardData);
        } elseif ($builder->transactionType === TransactionType::GET_ACCOUNT_DETAILS
                 && empty($builder->accountNumber)) {
            if(!empty($builder->externalId)){
                $xmlTrans->appendChild($xml->createElement('externalId', $builder->externalId));
            } elseif(!empty($builder->sourceEmail)){
                $xmlTrans->appendChild($xml->createElement('sourceEmail', $builder->sourceEmail));
            }
        }
        
        $transaction->appendChild($xmlTrans);
        
        $requestXML = $xml->saveXML($transaction);                
        $response = $this->doTransaction($requestXML);
        
        return $this->mapResponse($builder, $response);
    }
    
    public function mapRequestType($builder)
    {
        switch ($builder->transactionType) {
            case TransactionType::CREATE_ACCOUNT:
                return '01';
            case TransactionType::EDIT:
                return '42';
            case TransactionType::RESET_PASSWORD:
                return '32';
            case TransactionType::RENEW_ACCOUNT:
                return '39';
            case TransactionType::UPDATE_OWNERSHIP_DETAILS:
                return '44';
            case TransactionType::DEACTIVATE:
                return '41';
            case TransactionType::UPLOAD_CHARGEBACK_DOCUMENT:
                return '46';
            case TransactionType::UPLOAD_DOCUMENT:
                return '47';
            case TransactionType::OBTAIN_SSO_KEY:
                return '300';
            case TransactionType::UPDATE_BANK_ACCOUNT_OWNERSHIP:
                return '210';
            case TransactionType::ADD_FUNDS:
                return "37";
            case TransactionType::SWEEP_FUNDS:
                return "38";
            case TransactionType::ADD_CARD_FLASH_FUNDS:
                return "209";
            case TransactionType::PUSH_MONEY_FLASH_FUNDS:
                return "45";
            case TransactionType::DISBURSE_FUNDS:
                return "02";
            case TransactionType::SPEND_BACK:
                return "11";
            case TransactionType::REVERSE_SPLITPAY:
                return "43";
            case TransactionType::SPLIT_FUNDS:
                return "16";
            case TransactionType::GET_ACCOUNT_DETAILS:
                return "13";
            case TransactionType::GET_ACCOUNT_BALANCE:
                return "14";
            default:
                throw new UnsupportedTransactionException();
        }
    }
    
    public function mapResponse($builder, $rawResponse)
    {
        $root = $this->xml2object($rawResponse);
        
        $responseCode = !empty($root->status) ? $root->status : 'Invalid Status code';
        
        if ($responseCode != '00') {
            throw new GatewayException(
                sprintf(
                    'Unexpected Gateway Response: %s. ',
                    $responseCode
                )
            );
        }
        
        $response = new Transaction();
        $response->payFacData = $this->populateProPayResponse($root);
        $response->responseCode = (string) $root->status;
        
        return $response;
    }
    
    /**
     * Converts a XML string to a simple object for use,
     * removing extra nodes that are not necessary for
     * handling the response
     *
     * @param string $xml Response XML from the gateway
     *
     * @return SimpleXMLElement
     */
    protected function xml2object($xml)
    {
        $envelope = simplexml_load_string(
            $xml,
            'SimpleXMLElement'
        );
        
        return !empty($envelope->XMLTrans) ? $envelope->XMLTrans : null;
        
        throw new \Exception('XML from gateway could not be parsed');
    }
    
    
    private function hydrateAccountDetails($xml, $xmlTrans, PayFacBuilder $builder)
    {
        if (!empty($builder->userPersonalData)) {
            $this->hydrateUserPersonalData($xml, $xmlTrans, $builder->userPersonalData);
        }
        
        if (!empty($builder->businessData)) {
            $this->hydrateBusinessData($xml, $xmlTrans, $builder->businessData);
        }
                
        if (!empty($builder->beneficialOwnerData)) {
            $this->hydrateBeneficialOwnerData($xml, $xmlTrans, $builder->beneficialOwnerData);
        }
        
        if (!empty($builder->grossBillingInformation)) {
            $this->hydrateGrossBillingData($xml, $xmlTrans, $builder->grossBillingInformation);
        }
                
        $this->hydrateBankDetails($xml, $xmlTrans, $builder);
        $this->hydratOtherDetails($xml, $xmlTrans, $builder);
    }
    
    private function hydrateAccountEditDetails($xml, $xmlTrans, PayFacBuilder $builder)
    {
        if (!empty($builder->password)) {
            $xmlTrans->appendChild($xml->createElement('password', $builder->password));
        } elseif (!empty($builder->negativeLimit)) {
            $xmlTrans->appendChild($xml->createElement('negativelimit', $builder->negativeLimit));
        } elseif (!empty($builder->userPersonalData)) {
            $this->hydrateUserPersonalData($xml, $xmlTrans, $builder->userPersonalData);
        } elseif (!empty($builder->accountPermissions)) {
            $this->hydrateAccountPermissions($xml, $xmlTrans, $builder->accountPermissions);
        } elseif (!empty($builder->businessData)) {
            $this->hydrateBusinessData($xml, $xmlTrans, $builder->businessData);
        } elseif (!empty($builder->grossBillingInformation)) {
            $this->hydrateGrossBillingData($xml, $xmlTrans, $builder->grossBillingInformation);
        }
        //update bank details if any
        $this->hydrateBankDetails($xml, $xmlTrans, $builder);
    }
    
    private function hydrateAccountRenewDetails($xml, $xmlTrans, RenewAccountData $renewalAccountData)
    {
        $elementMap = [
            'tier' => !empty($renewalAccountData->tier) ? $renewalAccountData->tier : '',
            
            'CVV2' => !empty($renewalAccountData->creditCard->cvn) ? $renewalAccountData->creditCard->cvn : '',
            'ccNum' => !empty($renewalAccountData->creditCard->number) ? $renewalAccountData->creditCard->number : '',
            'expDate' => !empty($renewalAccountData->creditCard->expMonth) ? $renewalAccountData->creditCard->getShortExpiry() : '',
            'zip' => !empty($renewalAccountData->zipCode) ? $renewalAccountData->zipCode : '',
            
            'PaymentBankAccountNumber' => !empty($renewalAccountData->paymentBankAccountNumber) ? $renewalAccountData->paymentBankAccountNumber : '',
            'PaymentBankRoutingNumber' => !empty($renewalAccountData->paymentBankRoutingNumber) ? $renewalAccountData->paymentBankRoutingNumber : '',
            'PaymentBankAccountType' => !empty($renewalAccountData->paymentBankAccountType) ? $renewalAccountData->paymentBankAccountType : '',
        ];
        $this->createNewElements($xml, $xmlTrans, $elementMap);
    }
    
    private function hydrateBeneficialOwnerData($xml, $xmLtransaction, $beneficialOwnerData)
    {
        $ownerDetails = $xml->createElement('BeneficialOwnerData');
        $ownerDetails->appendChild($xml->createElement('OwnerCount', $beneficialOwnerData->ownersCount));
        
        if ($beneficialOwnerData->ownersCount > 0) {
            $ownersList = $xml->createElement('Owners');
            
            foreach ($beneficialOwnerData->ownersList as $ownerInfo) {
                $newOwner = $xml->createElement('Owner');
                
                $elements = [
                    'FirstName' => !empty($ownerInfo->firstName) ? $ownerInfo->firstName : '',
                    'LastName' => !empty($ownerInfo->lastName) ? $ownerInfo->lastName : '',
                    'Email' => !empty($ownerInfo->email) ? $ownerInfo->email : '',
                    'SSN' => !empty($ownerInfo->ssn) ? $ownerInfo->ssn : '',
                    'DateOfBirth' => !empty($ownerInfo->dateOfBirth) ? $ownerInfo->dateOfBirth : '',
                    'Address' => !empty($ownerInfo->ownerAddress->streetAddress1) ? $ownerInfo->ownerAddress->streetAddress1 : '',
                    'City' => !empty($ownerInfo->ownerAddress->city) ? $ownerInfo->ownerAddress->city : '',
                    'State' => !empty($ownerInfo->ownerAddress->state) ? $ownerInfo->ownerAddress->state : '',
                    'Zip' => !empty($ownerInfo->ownerAddress->postalCode) ? $ownerInfo->ownerAddress->postalCode : '',
                    'Country' => !empty($ownerInfo->ownerAddress->country) ? $ownerInfo->ownerAddress->country : '',
                    'Title' => !empty($ownerInfo->title) ? $ownerInfo->title : '',
                    'Percentage' => !empty($ownerInfo->percentage) ? $ownerInfo->percentage : ''
                ];
                
                $this->createNewElements($xml, $newOwner, $elements);
                $ownersList->appendChild($newOwner);
            }
            $ownerDetails->appendChild($ownersList);
        }
        
        $xmLtransaction->appendChild($ownerDetails);
    }
    
    private function hydrateBusinessData($xml, $transaction, BusinessData $businessData)
    {
        $propertyElementMap = [
            'BusinessLegalName' => !empty($businessData->businessLegalName) ? $businessData->businessLegalName : '',
            'DoingBusinessAs' => !empty($businessData->doingBusinessAs) ? $businessData->doingBusinessAs : '',
            'EIN' => !empty($businessData->employerIdentificationNumber) ? $businessData->employerIdentificationNumber : '',
            'MCCCode' => !empty($businessData->merchantCategoryCode) ? $businessData->merchantCategoryCode : '',
            'WebsiteURL' => !empty($businessData->websiteURL) ? $businessData->websiteURL : '',
            'BusinessDesc' => !empty($businessData->businessDescription) ? $businessData->businessDescription : '',
            'MonthlyBankCardVolume' => !empty($businessData->monthlyBankCardVolume) ? $businessData->monthlyBankCardVolume : '',
            'AverageTicket' => !empty($businessData->averageTicket) ? $businessData->averageTicket : '',
            'HighestTicket' => !empty($businessData->highestTicket) ? $businessData->highestTicket : '',
            'BusinessAddress' => !empty($businessData->businessAddress->streetAddress1) ? $businessData->businessAddress->streetAddress1 : '',
            'BusinessAddress2' => !empty($businessData->businessAddress->streetAddress2) ? $businessData->businessAddress->streetAddress2 : '',
            'BusinessCity' => !empty($businessData->businessAddress->city) ? $businessData->businessAddress->city : '',
            'BusinessCountry' => !empty($businessData->businessAddress->country) ? $businessData->businessAddress->country : '',
            'BusinessState' => !empty($businessData->businessAddress->state) ? $businessData->businessAddress->state : '',
            'BusinessZip' => !empty($businessData->businessAddress->postalCode) ? $businessData->businessAddress->postalCode : ''
        ];
        
        $this->createNewElements($xml, $transaction, $propertyElementMap);
    }
    
    private function hydrateUserPersonalData($xml, $transaction, UserPersonalData $userPersonalData)
    {
        $merchantAddress = $userPersonalData->userAddress;
        $mailingAddress = $userPersonalData->mailingAddress;
        
        $elementMap = [
            'firstName' => !empty($userPersonalData->firstName) ? $userPersonalData->firstName : '',
            'mInitial' => !empty($userPersonalData->mInitial) ? $userPersonalData->mInitial : '',
            'lastName' => !empty($userPersonalData->lastName) ? $userPersonalData->lastName : '',
            'dob' => !empty($userPersonalData->dateOfBirth) ? $userPersonalData->dateOfBirth : '',
            'ssn' => !empty($userPersonalData->ssn) ? $userPersonalData->ssn : '',
            'sourceEmail' => !empty($userPersonalData->sourceEmail) ? $userPersonalData->sourceEmail : '',
            'dayPhone' => !empty($userPersonalData->dayPhone) ? $userPersonalData->dayPhone : '',
            'evenPhone' => !empty($userPersonalData->eveningPhone) ? $userPersonalData->eveningPhone : '',
            'NotificationEmail' => !empty($userPersonalData->notificationEmail) ? $userPersonalData->notificationEmail : '',
            'currencyCode' => !empty($userPersonalData->currencyCode) ? $userPersonalData->currencyCode : '',
            'tier' => !empty($userPersonalData->tier) ? $userPersonalData->tier : '',
            'externalId' => !empty($userPersonalData->externalId) ? $userPersonalData->externalId : '',
        
            'addr' => !empty($merchantAddress->streetAddress1) ? $merchantAddress->streetAddress1 : '',
            'aptNum' => !empty($merchantAddress->streetAddress2) ? $merchantAddress->streetAddress2 : '',
            'addr3' => !empty($merchantAddress->streetAddress3) ? $merchantAddress->streetAddress3 : '',
            'city' => !empty($merchantAddress->city) ? $merchantAddress->city : '',
            'state' => !empty($merchantAddress->state) ? $merchantAddress->state : '',
            'zip' => !empty($merchantAddress->postalCode) ? $merchantAddress->postalCode : '',
            'country' => !empty($merchantAddress->country) ? $merchantAddress->country : '',
                      
            'mailAddr' => !empty($mailingAddress->streetAddress1) ? $mailingAddress->streetAddress1 : '',
            'mailApt' => !empty($mailingAddress->streetAddress2) ? $mailingAddress->streetAddress2: '',
            'mailAddr3' => !empty($mailingAddress->streetAddress3) ? $mailingAddress->streetAddress3 : '',
            'mailCity' => !empty($mailingAddress->city) ? $mailingAddress->city : '',
            'mailCountry' => !empty($mailingAddress->country) ? $mailingAddress->country : '',
            'mailState' => !empty($mailingAddress->state) ? $mailingAddress->state : '' ,
            'mailZip' => !empty($mailingAddress->postalCode) ? $mailingAddress->postalCode : '',
        ];
        
        $this->createNewElements($xml, $transaction, $elementMap);
    }
    
    private function hydrateBankDetails($xml, $xmlTrans, $builder)
    {
        $elementMap = [];
        
        if (!empty($builder->creditCardInformation)) {
            $elementMap = [
                'NameOnCard' => !empty($builder->creditCardInformation->cardHolderName) ? $builder->creditCardInformation->cardHolderName : '',
                'ccNum' => !empty($builder->creditCardInformation->number) ? $builder->creditCardInformation->number : '',
                'expDate' => !empty($builder->creditCardInformation->number) ? $builder->creditCardInformation->getShortExpiry() : '',
            ];
        }
        
        if (!empty($builder->achInformation)) {
            $achInformation = [
                'PaymentBankAccountNumber' => !empty($builder->achInformation->accountNumber) ? $builder->achInformation->accountNumber : '',
                'PaymentBankRoutingNumber' => !empty($builder->achInformation->routingNumber) ? $builder->achInformation->routingNumber : '',
                'PaymentBankAccountType' => !empty($builder->achInformation->accountType) ? $builder->achInformation->accountType : '',
            ];
            
            $elementMap = array_merge($elementMap, $achInformation);
        }
        
        if (!empty($builder->bankAccountData)) {
            $bankAccountData = [
                'AccountCountryCode' => !empty($builder->bankAccountData->accountCountryCode) ? $builder->bankAccountData->accountCountryCode : '',
                'accountName' => !empty($builder->bankAccountData->accountName) ? $builder->bankAccountData->accountName : '',
                'AccountNumber' => !empty($builder->bankAccountData->accountNumber) ? $builder->bankAccountData->accountNumber : '',
                'AccountOwnershipType' => !empty($builder->bankAccountData->accountOwnershipType) ? $builder->bankAccountData->accountOwnershipType : '',
                'accountType' => !empty($builder->bankAccountData->accountType) ? $builder->bankAccountData->accountType : '',
                'BankName' => !empty($builder->bankAccountData->bankName) ? $builder->bankAccountData->bankName : '',
                'RoutingNumber' => !empty($builder->bankAccountData->routingNumber) ? $builder->bankAccountData->routingNumber : '',
            ];
            
            $elementMap = array_merge($elementMap, $bankAccountData);
        }
        
        if (!empty($builder->secondaryBankInformation)) {
            $secondaryBankInformation = [
                'SecondaryAccountCountryCode' => !empty($builder->secondaryBankInformation->accountCountryCode) ? $builder->secondaryBankInformation->accountCountryCode : '',
                'SecondaryAccountName' => !empty($builder->secondaryBankInformation->accountName) ? $builder->secondaryBankInformation->accountName : '',
                'SecondaryAccountNumber' => !empty($builder->secondaryBankInformation->accountNumber) ? $builder->secondaryBankInformation->accountNumber : '',
                'SecondaryAccountOwnershipType' => !empty($builder->secondaryBankInformation->accountOwnershipType) ? $builder->secondaryBankInformation->accountOwnershipType : '',
                'SecondaryAccountType' => !empty($builder->secondaryBankInformation->accountType) ? $builder->secondaryBankInformation->accountType : '',
                'SecondaryBankName' => !empty($builder->secondaryBankInformation->bankName) ? $builder->secondaryBankInformation->bankName : '',
                'SecondaryRoutingNumber' => !empty($builder->secondaryBankInformation->routingNumber) ? $builder->secondaryBankInformation->routingNumber : ''
            ];
            
            $elementMap = array_merge($elementMap, $secondaryBankInformation);
        }
        
        $this->createNewElements($xml, $xmlTrans, $elementMap);
    }
    
    private function hydratOtherDetails($xml, $xmlTrans, $builder)
    {
        //threatRiskData, significantOwnerData
        $significantData = $builder->significantOwnerData;
        $details = [
            'MerchantSourceip' => !empty($builder->threatRiskData->merchantSourceIp) ? $builder->threatRiskData->merchantSourceIp : '',
            'ThreatMetrixPolicy' => !empty($builder->threatRiskData->threatMetrixPolicy) ? $builder->threatRiskData->threatMetrixPolicy : '',
            'ThreatMetrixSessionid' => !empty($builder->threatRiskData->threatMetrixSessionId) ? $builder->threatRiskData->threatMetrixSessionId : '',
            'AuthorizedSignerFirstName' => !empty($significantData->authorizedSignerFirstName) ? $significantData->authorizedSignerFirstName : '',
            'AuthorizedSignerLastName' => !empty($significantData->authorizedSignerLastName) ? $significantData->authorizedSignerLastName : '',
            'AuthorizedSignerTitle' => !empty($significantData->authorizedSignerTitle) ? $significantData->authorizedSignerTitle : '',
            ];
        
        if (!empty($significantData->significantOwnerData)) {
            $significantOwnerData = $significantData->significantOwnerData;
            $details = array_merge($details, [
                'SignificantOwnerFirstName' => !empty($significantOwnerData->firstName) ? $significantOwnerData->firstName : '',
                'SignificantOwnerLastName' => !empty($significantOwnerData->lastName) ? $significantOwnerData->lastName : '',
                'SignificantOwnerSSN' => !empty($significantOwnerData->ssn) ? $significantOwnerData->ssn : '',
                'SignificantOwnerDateOfBirth' => !empty($significantOwnerData->dateOfBirth) ? $significantOwnerData->dateOfBirth : '',
                'SignificantOwnerStreetAddress' => !empty($significantOwnerData->ownerAddress->streetAddress1) ? $significantOwnerData->ownerAddress->streetAddress1 : '',
                'SignificantOwnerCityName' => !empty($significantOwnerData->ownerAddress->city) ? $significantOwnerData->ownerAddress->city : '',
                'SignificantOwnerRegionCode' => !empty($significantOwnerData->ownerAddress->state) ? $significantOwnerData->ownerAddress->state : '',
                'SignificantOwnerPostalCode' => !empty($significantOwnerData->ownerAddress->postalCode) ? $significantOwnerData->ownerAddress->postalCode : '',
                'SignificantOwnerCountryCode' => !empty($significantOwnerData->ownerAddress->country) ? $significantOwnerData->ownerAddress->country : '',
                'SignificantOwnerTitle' => !empty($significantOwnerData->title) ? $significantOwnerData->title : '',
                'SignificantOwnerPercentage' => !empty($significantOwnerData->percentage) ? $significantOwnerData->percentage : '',
            ]);
        }
        
        $this->createNewElements($xml, $xmlTrans, $details);
    }
    
    private function hydrateGrossBillingData($xml, $transaction, $grossBilling)
    {
        $propertyElementMap = [
            'GrossSettleAddress' => !empty($grossBilling->grossSettleAddress->streetAddress1) ? $grossBilling->grossSettleAddress->streetAddress1 : '',
            'GrossSettleCity' => !empty($grossBilling->grossSettleAddress->city) ? $grossBilling->grossSettleAddress->city : '',
            'GrossSettleState' => !empty($grossBilling->grossSettleAddress->state) ? $grossBilling->grossSettleAddress->state : '',
            'GrossSettleZipCode' => !empty($grossBilling->grossSettleAddress->postalCode) ? $grossBilling->grossSettleAddress->postalCode : '',
            'GrossSettleCountry' => !empty($grossBilling->grossSettleAddress->country) ? $grossBilling->grossSettleAddress->country : '',
            
            'GrossSettleCreditCardNumber' => !empty($grossBilling->grossSettleCreditCardData->number) ? $grossBilling->grossSettleCreditCardData->number : '',
            'GrossSettleNameOnCard' => !empty($grossBilling->grossSettleCreditCardData->cardHolderName) ? $grossBilling->grossSettleCreditCardData->cardHolderName : '',
            'GrossSettleCreditCardExpDate' => $grossBilling->grossSettleCreditCardData->getShortExpiry(),
            
            'GrossSettleAccountCountryCode' => !empty($grossBilling->grossSettleBankData->accountCountryCode) ? $grossBilling->grossSettleBankData->accountCountryCode : '',
            'GrossSettleAccountHolderName' => !empty($grossBilling->grossSettleBankData->accountName) ? $grossBilling->grossSettleBankData->accountName : '',
            'GrossSettleAccountNumber' => !empty($grossBilling->grossSettleBankData->accountNumber) ? $grossBilling->grossSettleBankData->accountNumber : '',
            'GrossSettleAccountType' => !empty($grossBilling->grossSettleBankData->accountType) ? $grossBilling->grossSettleBankData->accountType : '',
            'GrossSettleRoutingNumber' => !empty($grossBilling->grossSettleBankData->routingNumber) ? $grossBilling->grossSettleBankData->routingNumber : ''
        ];
        
        $this->createNewElements($xml, $transaction, $propertyElementMap);
    }
    
    private function hydrateAccountPermissions($xml, $xmlTrans, AccountPermissions $accountPermissions)
    {
        $propertyElementMap = [
            'ACHIn' => !empty($accountPermissions->achIn) ? $accountPermissions->achIn : '',
            'ACHOut' => !empty($accountPermissions->achOut) ? $accountPermissions->achOut : '',
            'CCProcessing' => !empty($accountPermissions->ccProcessing) ? $accountPermissions->ccProcessing : '',
            'ProPayIn' => !empty($accountPermissions->proPayIn) ? $accountPermissions->proPayIn : '',
            'ProPayOut' => !empty($accountPermissions->proPayOut) ? $accountPermissions->proPayOut : '',
            'CreditCardMonthLimit' => !empty($accountPermissions->creditCardMonthLimit) ? $accountPermissions->creditCardMonthLimit : '',
            'CreditCardTransactionLimit' => !empty($accountPermissions->creditCardTransactionLimit) ? $accountPermissions->creditCardTransactionLimit : '',
            'MerchantOverallStatus' => !empty($accountPermissions->merchantOverallStatus) ? $accountPermissions->merchantOverallStatus : '',
            'SoftLimitEnabled' => !empty($accountPermissions->softLimitEnabled) ? $accountPermissions->softLimitEnabled : '',
            'AchPaymentSoftLimitEnabled' => !empty($accountPermissions->achPaymentSoftLimitEnabled) ? $accountPermissions->achPaymentSoftLimitEnabled : '',
            'SoftLimitAchOffPercent' => !empty($accountPermissions->softLimitAchOffPercent) ? $accountPermissions->softLimitAchOffPercent : '',
            'AchPaymentAchOffPercent' => !empty($accountPermissions->achPaymentAchOffPercent) ? $accountPermissions->achPaymentAchOffPercent : '',
        ];
        
        $this->createNewElements($xml, $xmlTrans, $propertyElementMap);
    }
    
    private function createNewElements($xml, $transaction, $mapping)
    {
        foreach ($mapping as $tagName => $value) {
            if (!is_null($value) && $value !== '') {
                $transaction->appendChild($xml->createElement($tagName, $value));
            }
        }
    }

    private function setx509Certificate($certX509File)
    {
        try {
            $cert = file_get_contents($certX509File);
            return base64_encode($cert);
        } catch (\Exception $e) {
            throw new GatewayException(
                'X509 certificate error: ',
                $e->getCode(),
                $e->getMessage(),
                $e
            );
        }
    }
    
    private function updateGatewaySettings($builder)
    {
        $certTransactions = [
            TransactionType::EDIT,
            TransactionType::OBTAIN_SSO_KEY,
            TransactionType::UPDATE_BANK_ACCOUNT_OWNERSHIP,
            TransactionType::ADD_FUNDS,
            TransactionType::ADD_CARD_FLASH_FUNDS,
        ];
        
        if (in_array($builder->transactionType, $certTransactions)) {
            $this->headers['X509Certificate'] = $this->setx509Certificate($this->selfSignedCert);
        }
    }
    
    private function hydrateUploadDocument($xml, $xmlTrans, UploadDocumentData $uploadDocumentData)
    {
        $fileType = pathinfo($uploadDocumentData->documentLocation, PATHINFO_EXTENSION);
        $elementMap = [
            'DocumentName' => !empty($uploadDocumentData->documentName) ? $uploadDocumentData->documentName : '',
            'TransactionReference' => !empty($uploadDocumentData->transactionReference) ? $uploadDocumentData->transactionReference : '',
            'DocType' => $fileType,
            'Document' => base64_encode(file_get_contents($uploadDocumentData->documentLocation)),
            'DocCategory' => !empty($uploadDocumentData->documentCategory) ? $uploadDocumentData->documentCategory : '',
        ];
        $this->createNewElements($xml, $xmlTrans, $elementMap);
    }
    
    private function hydrateSingleSignOnData($xml, $xmlTrans, SingleSignOnData $singleSignOnData)
    {
        $elementMap = [
            'ReferrerUrl' => !empty($singleSignOnData->referrerUrl) ? $singleSignOnData->referrerUrl : '',
            'IpAddress' => !empty($singleSignOnData->ipAddress) ? $singleSignOnData->ipAddress : '',
            'IpSubnetMask' => !empty($singleSignOnData->ipSubnetMask) ? $singleSignOnData->ipSubnetMask : '',
        ];
        $this->createNewElements($xml, $xmlTrans, $elementMap);
    }
    
    private function updateBankAccountOwnershipInfo($xml, $xmLtransaction, $beneficialOwnerData)
    {
        $ownerDetails = $xml->createElement('BankAccountOwnerData');
        
        if (!empty($beneficialOwnerData->ownersList)) {
            foreach ($beneficialOwnerData->ownersList as $key => $ownerInfo) {
                $ownerType = ($key === 0) ? 'PrimaryBankAccountOwner' : 'SecondaryBankAccountOwner';
                $newOwner = $xml->createElement($ownerType);
                
                $elements = [
                    'FirstName' => !empty($ownerInfo->firstName) ? $ownerInfo->firstName : '',
                    'LastName' => !empty($ownerInfo->lastName) ? $ownerInfo->lastName : '',
                    'Address1' => !empty($ownerInfo->ownerAddress->streetAddress1) ? $ownerInfo->ownerAddress->streetAddress1 : '',
                    'Address2' => !empty($ownerInfo->ownerAddress->streetAddress2) ? $ownerInfo->ownerAddress->streetAddress2 : '',
                    'Address3' => !empty($ownerInfo->ownerAddress->streetAddress3) ? $ownerInfo->ownerAddress->streetAddress3 : '',
                    'City' => !empty($ownerInfo->ownerAddress->city) ? $ownerInfo->ownerAddress->city : '',
                    'StateProvince' => !empty($ownerInfo->ownerAddress->state) ? $ownerInfo->ownerAddress->state : '',
                    'PostalCode' => !empty($ownerInfo->ownerAddress->postalCode) ? $ownerInfo->ownerAddress->postalCode : '',
                    'Country' => !empty($ownerInfo->ownerAddress->country) ? $ownerInfo->ownerAddress->country : '',
                    'Phone' => !empty($ownerInfo->phone) ? $ownerInfo->phone : '',
                ];
                
                $this->createNewElements($xml, $newOwner, $elements);
                $ownerDetails->appendChild($newOwner);
            }
        }
        
        $xmLtransaction->appendChild($ownerDetails);
    }
    
    private function hydrateCommonFields($xml, $xmlTrans, $builder)
    {
        $elementMap = [
            'accountNum' => !empty($builder->accountNumber) ? $builder->accountNumber : '',
            'amount' => !empty($builder->amount) ? $builder->amount : '',
            'recAccntNum' => !empty($builder->receivingAccountNumber) ? $builder->receivingAccountNumber : '',
            'allowPending' => !empty($builder->allowPending) ? 'Y' : '',
            'ccAmount' => !empty($builder->ccAmount) ? $builder->ccAmount : '',
            'requireCCRefund' => !empty($builder->requireCCRefund) ? 'Y' : 'N',
            'transNum' => !empty($builder->transNum) ? $builder->transNum : ''
        ];
        $this->createNewElements($xml, $xmlTrans, $elementMap);
    }
    
    private function hydrateFlashFundsData($xml, $xmlTrans, FlashFundsPaymentCardData $flashFundsData)
    {
        $elementMap = [
            'ccNum' => !empty($flashFundsData->creditCard->number) ? $flashFundsData->creditCard->number : '',
            'expDate' => !empty($flashFundsData->creditCard->expMonth) ? $flashFundsData->creditCard->getShortExpiry() : '',
            'CVV2' => !empty($flashFundsData->creditCard->cvn) ? $flashFundsData->creditCard->cvn : '',
            'cardholderName' => !empty($flashFundsData->creditCard->cardHolderName) ? $flashFundsData->creditCard->cardHolderName : '',
            'addr' => !empty($flashFundsData->cardholderAddress->streetAddress1) ? $flashFundsData->cardholderAddress->streetAddress1 : '',
            'city' => !empty($flashFundsData->cardholderAddress->city) ? $flashFundsData->cardholderAddress->city : '',
            'state' => !empty($flashFundsData->cardholderAddress->state) ? $flashFundsData->cardholderAddress->state : '',
            'zip' => !empty($flashFundsData->cardholderAddress->postalCode) ? $flashFundsData->cardholderAddress->postalCode : '',
        ];
        $this->createNewElements($xml, $xmlTrans, $elementMap);
    }
    
    private function populateProPayResponse($root)
    {
        $propayResponse = new PayFacResponseData();
        $propayResponse->password = (!empty($root->password)) ? (string) $root->password : '';
        $propayResponse->sourceEmail = (!empty($root->sourceEmail)) ? (string) $root->sourceEmail : '';
        $propayResponse->authToken = (!empty($root->AuthToken)) ? (string) $root->AuthToken : '';
        $propayResponse->recAccountNum = !empty($root->recAccntNum) ? (string) $root->recAccntNum : '';
        $propayResponse->amount = !empty($root->amount) ? (string) $root->amount : '';
        $propayResponse->transNum = !empty($root->transNum) ? (string) $root->transNum : '';
        $propayResponse->pending = !empty($root->pending) ? (string) $root->pending : '';
        $propayResponse->secondaryAmount = !empty($root->secondaryAmount) ? (string) $root->secondaryAmount : '';
        $propayResponse->secondaryTransNum = !empty($root->secondaryTransNum) ? (string) $root->secondaryTransNum : '';
        $propayResponse->accountStatus = !empty($root->accntStatus) ? (string) $root->accntStatus : '';
        $propayResponse->affiliation = !empty($root->affiliation) ? (string) $root->affiliation : '';
        $propayResponse->aPIReady = !empty($root->apiReady) ? (string) $root->apiReady : '';
        $propayResponse->currencyCode = !empty($root->currencyCode) ? (string) $root->currencyCode : '';
        $propayResponse->expiration = !empty($root->expiration) ? (string) $root->expiration : '';
        $propayResponse->signupDate = !empty($root->signupDate) ? (string) $root->signupDate : '';
        $propayResponse->visaCheckoutMerchantID = !empty($root->visaCheckoutMerchantId) ? (string) $root->visaCheckoutMerchantId : '';
        $propayResponse->creditCardTransactionLimit = !empty($root->CreditCardTransactionLimit) ? (string) $root->CreditCardTransactionLimit : '';
        $propayResponse->creditCardMonthLimit = !empty($root->CreditCardMonthLimit) ? (string) $root->CreditCardMonthLimit : '';
        $propayResponse->aCHPaymentPerTranLimit = !empty($root->ACHPaymentPerTranLimit) ? (string) $root->ACHPaymentPerTranLimit : '';
        $propayResponse->aCHPaymentMonthLimit = !empty($root->ACHPaymentMonthLimit) ? (string) $root->ACHPaymentMonthLimit : '';
        $propayResponse->creditCardMonthlyVolume = !empty($root->CreditCardMonthlyVolume) ? (string) $root->CreditCardMonthlyVolume : '';
        $propayResponse->aCHPaymentMonthlyVolume = !empty($root->ACHPaymentMonthlyVolume) ? (string) $root->ACHPaymentMonthlyVolume : '';
        $propayResponse->reserveBalance = !empty($root->ReserveBalance) ? (string) $root->ReserveBalance : '';
        $propayResponse->masterPassCheckoutMerchantID = !empty($root->MasterPassCheckoutMerchantId) ? (string) $root->MasterPassCheckoutMerchantId : '';
        $propayResponse->pendingAmount = !empty($root->pendingAmount) ? (string) $root->pendingAmount : '';
        $propayResponse->reserveAmount = !empty($root->reserveAmount) ? (string) $root->reserveAmount : '';
        
        $propayResponse->physicalAddress->streetAddress1 = !empty($root->addr) ? (string) $root->addr : '';
        $propayResponse->physicalAddress->city = !empty($root->city) ? (string) $root->city : '';
        $propayResponse->physicalAddress->state = !empty($root->state) ? (string) $root->state : '';
        $propayResponse->physicalAddress->postalCode = !empty($root->zip) ? (string) $root->zip : '';
        
        if (!empty($root->accntNum)) {
            $propayResponse->accountNumber = (string) $root->accntNum;
        } elseif (!empty($root->accountNum)) {
            $propayResponse->accountNumber = (string) $root->accountNum;
        }
        
        if (!empty($root->achOut)) {
            $propayResponse->aCHOut->enabled = !empty($root->achOut->enabled) ? (string) $root->achOut->enabled : '';
            $propayResponse->aCHOut->limitRemaining = !empty($root->achOut->limitRemaining) ? (string) $root->achOut->limitRemaining : '';
            $propayResponse->aCHOut->transferFee = !empty($root->achOut->transferFee) ? (string) $root->achOut->transferFee : '';
            $propayResponse->aCHOut->feeType = !empty($root->achOut->feeType) ? (string) $root->achOut->feeType : '';
            $propayResponse->aCHOut->accountLastFour = !empty($root->achOut->accountLastFour) ? (string) $root->achOut->accountLastFour : '';
        }
        
        if (!empty($root->flashFunds)) {
            $propayResponse->flashFunds->enabled = !empty($root->flashFunds->enabled) ? (string) $root->flashFunds->enabled : '';
            $propayResponse->flashFunds->limitRemaining = !empty($root->flashFunds->limitRemaining) ? (string) $root->flashFunds->limitRemaining : '';
            $propayResponse->flashFunds->transferFee = !empty($root->flashFunds->transferFee) ? (string) $root->flashFunds->transferFee : '';
            $propayResponse->flashFunds->feeType = !empty($root->flashFunds->feeType) ? (string) $root->flashFunds->feeType : '';
            $propayResponse->flashFunds->accountLastFour = !empty($root->flashFunds->accountLastFour) ? (string) $root->flashFunds->accountLastFour : '';
        }
        
        if (!empty($root->beneficialOwnerDataResult->Owner)) {
            foreach ($root->beneficialOwnerDataResult->Owner as $owner) {
                $ownerDetails = new OwnerDetailsResponseData();                
                $ownerDetails->firstName = (string) $owner->FirstName;
                $ownerDetails->lastName = (string) $owner->LastName;
                $ownerDetails->validationStatus = (string) $owner->Status;
                $propayResponse->beneficialOwnerDataResult[] = $ownerDetails;
            }
        }
        
        return $propayResponse;
    }
}
