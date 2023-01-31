<?php

namespace GlobalPayments\Api\Gateways;

use GlobalPayments\Api\Builders\RecurringBuilder;
use GlobalPayments\Api\Entities\{Address, Customer, Schedule};
use GlobalPayments\Api\Entities\Enums\{AccountType, CheckType, PaymentSchedule, SecCode, TransactionType};
use GlobalPayments\Api\Entities\Exceptions\{ArgumentException, UnsupportedTransactionException};
use GlobalPayments\Api\PaymentMethods\{Credit, Echeck, RecurringPaymentMethod};
use GlobalPayments\Api\PaymentMethods\Interfaces\{ICardData, IEncryptable, IPaymentMethod, ITrackData};

class PayPlanConnector extends RestGateway implements IRecurringService
{
    /**
     * Site ID to authenticate with the gateway
     *
     * @var string
     */
    public $siteId;

    /**
     * License ID to authenticate with the gateway
     *
     * @var string
     */
    public $licenseId;

    /**
     * Device ID to authenticate with the gateway
     *
     * @var string
     */
    public $deviceId;

    /**
     * Username to authenticate with the gateway
     *
     * @var string
     */
    public $username;

    /**
     * Password to authenticate with the gateway
     *
     * @var string
     */
    public $password;

    /**
     * Secret API Key to authenticate with the gateway.
     *
     * This can be used in place of the following properties:
     *
     * - username
     * - password
     * - siteId
     * - licenseId
     * - deviceId
     *
     * @var string
     */
    public $apiKey;

    /**
     * Developer ID for the application, as given during certification
     *
     * @var string
     */
    protected $devId;

    /**
     * Version number for the application, as given during certification
     *
     * @var string
     */
    protected $versionNbr;

    public $supportsRetrieval = true;
    public $supportsUpdatePaymentDetails = false;

    protected $integrationHeader = array();

    public function __get($name)
    {
        switch ($name) {
            case 'secretApiKey':
                return $this->apiKey;
            case 'developerId':
                return $this->devId;
            case 'versionNumber':
                return $this->versionNbr;
            default:
                break;
        }

        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        throw new ArgumentException(sprintf('Property `%s` does not exist on PayPlanConnector', $name));
    }

    public function __isset($name)
    {
        return in_array($name, [
            'secretApiKey',
        ]) || isset($this->{$name});
    }

    public function __set($name, $value)
    {
        switch ($name) {
            case 'secretApiKey':
                $this->apiKey = $value;
                $auth = sprintf('Basic %s', base64_encode($value));
                $this->headers['Authorization'] = $auth;
                return;
            case 'developerId':
                $this->devId = $value;
                $this->integrationHeader['DeveloperId'] = $value;
                $this->updateIntegrationHeader();
                return;
            case 'versionNumber':
                $this->versionNbr = $value;
                $this->integrationHeader['VersionNbr'] = $value;
                $this->updateIntegrationHeader();
                return;
            default:
                break;
        }

        if (property_exists($this, $name)) {
            return $this->{$name} = $value;
        }

        throw new ArgumentException(sprintf('Property `%s` does not exist on PayPlanConnector', $name));
    }

    public function processRecurring(RecurringBuilder $builder)
    {
        $request = [];

        if ($builder->transactionType === TransactionType::CREATE
            || $builder->transactionType === TransactionType::EDIT
        ) {
            if ($builder->entity instanceof Customer) {
                $request = $this->buildCustomer($request, $builder->entity);
            }

            if ($builder->entity instanceof RecurringPaymentMethod) {
                $request = $this->buildPaymentMethod($request, $builder->entity, $builder->transactionType);
            }

            if ($builder->entity instanceof Schedule) {
                $request = $this->buildSchedule($request, $builder->entity, $builder->transactionType);
            }
        }

        if ($builder->transactionType === TransactionType::SEARCH) {
            foreach ($builder->searchCriteria as $key => $value) {
                $request[$key] = $value;
            }
        }

        if ($builder->transactionType !== TransactionType::EDIT) {
            foreach ($request as $key => $value) {
                if ($value !== 0 && empty($value)) {
                    unset($request[$key]);
                }
            }
        }
    
        $response = $this->doTransaction(
            $this->mapMethod($builder->transactionType),
            $this->mapUrl($builder),
            $request === [] ? '{}' : json_encode($request)
        );
        return $this->mapResponse($builder, $response);
    }

    #region Mappers
    protected function mapResponse($builder, $rawResponse)
    {
        // this is for DELETE which returns nothing
        if (empty($rawResponse)) {
            return null;
        }

        // else do the whole shebang
        $response = json_decode($rawResponse);

        if ($builder->entity instanceof Customer
            && $builder->transactionType === TransactionType::SEARCH
        ) {
            $customers = [];
            foreach ($response->results as $customer) {
                $customers[] = $this->hydrateCustomer($customer);
            }
            return $customers;
        }

        if ($builder->entity instanceof Customer) {
            return $this->hydrateCustomer($response);
        }

        if ($builder->entity instanceof RecurringPaymentMethod
            && $builder->transactionType === TransactionType::SEARCH
        ) {
            $methods = [];
            foreach ($response->results as $method) {
                $methods[] = $this->hydratePaymentMethod($method);
            }
            return $methods;
        }

        if ($builder->entity instanceof RecurringPaymentMethod) {
            return $this->hydratePaymentMethod($response);
        }

        if ($builder->entity instanceof Schedule
            && $builder->transactionType === TransactionType::SEARCH
        ) {
            $schedules = [];
            foreach ($response->results as $schedule) {
                $schedules[] = $this->hydrateSchedule($schedule);
            }
            return $schedules;
        }

        if ($builder->entity instanceof Schedule) {
            return $this->hydrateSchedule($response);
        }

        return $response;
    }

    protected function mapMethod($type)
    {
        switch ($type) {
            case TransactionType::CREATE:
            case TransactionType::SEARCH:
                return 'POST';
            case TransactionType::EDIT:
                return 'PUT';
            case TransactionType::DELETE:
                return 'DELETE';
            default:
                return 'GET';
        }
    }

    protected function mapUrl(RecurringBuilder $builder)
    {
        $suffix = '';
        if ($builder->transactionType === TransactionType::FETCH
            || $builder->transactionType === TransactionType::DELETE
            || $builder->transactionType === TransactionType::EDIT
        ) {
            $suffix = '/' . $builder->entity->key;
        }

        if ($builder->entity instanceof Customer) {
            return sprintf(
                '%s%s',
                $builder->transactionType === TransactionType::SEARCH ? 'searchCustomers' : 'customers',
                $suffix
            );
        }

        if ($builder->entity instanceof RecurringPaymentMethod) {
            $paymentMethod = '';
            if ($builder->transactionType === TransactionType::CREATE) {
                $paymentMethod = $builder->entity->paymentMethod instanceof Credit ? 'CreditCard' : 'ACH';
            } elseif ($builder->transactionType === TransactionType::EDIT) {
                $paymentMethod = str_replace(' ', '', $builder->entity->paymentType);
            }
            return sprintf(
                '%s%s%s',
                $builder->transactionType === TransactionType::SEARCH ? 'searchPaymentMethods' : 'paymentMethods',
                $paymentMethod,
                $suffix
            );
        }

        if ($builder->entity instanceof Schedule) {
            return sprintf(
                '%s%s',
                $builder->transactionType === TransactionType::SEARCH ? 'searchSchedules' : 'schedules',
                $suffix
            );
        }

        throw new UnsupportedTransactionException();
    }
    #endregion

    #region Build Entities
    protected function buildCustomer($request, Customer $customer = null)
    {
        if ($customer === null) {
            return $request;
        }

        $request['customerIdentifier'] = $customer->id;
        $request['firstName'] = $customer->firstName;
        $request['lastName'] = $customer->lastName;
        $request['company'] = $customer->company;
        $request['customerStatus'] = $customer->status;
        $request['primaryEmail'] = $customer->email;
        $request['phoneDay'] = $customer->homePhone;
        $request['phoneEvening'] = $customer->workPhone;
        $request['phoneMobile'] = $customer->mobilePhone;
        $request['fax'] = $customer->fax;
        $request['title'] = $customer->title;
        $request['department'] = $customer->department;
        $request = $this->buildAddress($request, $customer->address);

        return $request;
    }

    protected function buildPaymentMethod($request, RecurringPaymentMethod $payment, $type)
    {
        if ($payment === null) {
            return $request;
        }

        $request['preferredPayment'] = $payment->preferredPayment;
        $request['paymentMethodIdentifier'] = $payment->id;
        $request['customerKey'] = $payment->customerKey;
        $request['nameOnAccount'] = $payment->nameOnAccount;
        $request = $this->buildAddress($request, $payment->address);

        if ($type === TransactionType::CREATE) {
            list($hasToken, $tokenValue) = $this->hasToken($payment->paymentMethod);
            $paymentInfo = null;
            $paymentInfoKey = null;
            if ($payment->paymentMethod instanceof ICardData) {
                $method = $payment->paymentMethod;
                $paymentInfoKey = $hasToken ? 'alternateIdentity' : 'card';
                $paymentInfo = [
                    $hasToken ? 'token' : 'number' => $hasToken ? $tokenValue : $method->number,
                    'expMon' => $method->expMonth,
                    'expYear' => $method->expYear,
                ];

                if ($hasToken) {
                    $paymentInfo['type'] = 'SINGLEUSETOKEN';
                }

                $request['cardVerificationValue'] = $method->cvn;
            } elseif ($payment->paymentMethod instanceof ITrackData) {
                $method = $payment->paymentMethod;
                $paymentInfoKey = 'track';
                $paymentInfo = [
                    'data' => $method->value,
                    'dataEntryMode' => strtoupper($method->entryMethod),
                ];
            }

            if ($payment->paymentMethod instanceof ECheck) {
                $check = $payment->paymentMethod;
                $request['achType'] = $this->mapAccountType($check->accountType);
                $request['accountType'] = $this->mapCheckType($check->checkType);
                $request['telephoneIndicator'] =
                    $check->secCode === SecCode::CCD || $check->secCode == SecCode::PPD
                    ? false
                    : true;
                $request['routingNumber'] = $check->routingNumber;
                $request['accountNumber'] = $check->accountNumber;
                $request['accountHolderYob'] = $check->birthYear;
                $request['driversLicenseState'] = $check->driversLicenseState;
                $request['driversLicenseNumber'] = $check->driversLicenseNumber;
                $request['socialSecurityNumberLast4'] = $check->ssnLast4;
                unset($request['country']);
            }

            if ($payment->paymentMethod instanceof IEncryptable) {
                $enc = $payment->paymentMethod->encryptionData;
                if ($enc != null) {
                    $paymentInfo['trackNumber'] = $enc->trackNumber;
                    $paymentInfo['key'] = $enc->ktb;
                    $paymentInfo['encryptionType'] = 'E3';
                }
            }
        } else { // EDIT FIELDS
            unset($request['customerKey']);
            $request['paymentStatus'] = $payment->status;
            $request['cpcTaxType'] = $payment->taxType;
            $request['expirationDate'] = $payment->expirationDate;
        }

        if ($paymentInfo !== null) {
            $request[$paymentInfoKey] = $paymentInfo;
        }

        return $request;
    }

    protected function mapAccountType($type)
    {
        switch ($type) {
            case AccountType::CHECKING:
                return 'Checking';
            case AccountType::SAVINGS:
                return 'Savings';
        }
    }

    protected function mapCheckType($type)
    {
        switch ($type) {
            case CheckType::PERSONAL:
                return 'Personal';
            case CheckType::BUSINESS:
                return 'Business';
        }
    }

    protected function buildSchedule($request, Schedule $schedule, $type)
    {
        $mapDuration = function () use ($schedule) {
            if ($schedule->numberOfPaymentsRemaining !== null) {
                return 'Limited Number';
            }

            if ($schedule->endDate !== null) {
                return 'End Date';
            }

            return 'Ongoing';
        };

        $mapProcessingDate = function () use ($schedule) {
            $frequencies = [ 'Monthly', 'Bi-Monthly', 'Quarterly', 'Semi-Annually' ];
            if (in_array($schedule->frequency, $frequencies)) {
                switch ($schedule->paymentSchedule) {
                    case PaymentSchedule::FIRST_DAY_OF_THE_MONTH:
                        return 'First';
                    case PaymentSchedule::LAST_DAY_OF_THE_MONTH:
                        return 'Last';
                    default:
                        if (is_string($schedule->startDate) && !empty($schedule->startDate)) {
                            $day = intval(substr($schedule->startDate, 2, 2));
                        } else {
                            $day = intval($schedule->startDate->format('d'));
                        }
                        return $day > 28 ? 'Last' : $day;
                }
            }

            if ($schedule->frequency == 'Semi-Monthly') {
                return $schedule->paymentSchedule === PaymentSchedule::LAST_DAY_OF_THE_MONTH
                    ? 'Last'
                    : 'First';
            }

            return null;
        };

        if ($schedule === null) {
            return $request;
        }

        $request['scheduleName'] = $schedule->name;
        $request['scheduleStatus'] = $schedule->status;
        $request['paymentMethodKey'] = $schedule->paymentKey;

        $request = $this->buildAmount($request, 'subtotalAmount', $schedule->amount, $schedule->currency, $type);
        $request = $this->buildAmount($request, 'taxAmount', $schedule->taxAmount, $schedule->currency, $type);

        $request['deviceId'] = $schedule->deviceId;
        $request['processingDateInfo'] = $mapProcessingDate();
        $request = $this->buildDate($request, 'endDate', $schedule->endDate, ($type === TransactionType::EDIT));
        $request['reprocessingCount'] = $schedule->reprocessingCount ?: 3;
        $request['emailReceipt'] = $schedule->emailReceipt;
        $request['emailAdvanceNotice'] = $schedule->emailNotification ? 'Yes' : 'No';
        // debt repay ind
        $request['invoiceNbr'] = $schedule->invoiceNumber;
        $request['poNumber'] = $schedule->poNumber;
        $request['description'] = $schedule->description;
        $request['numberOfPaymentsRemaining'] = $schedule->numberOfPaymentsRemaining;

        if ($type === TransactionType::CREATE) {
            $request['customerKey'] = $schedule->customerKey;
            $request = $this->buildDate($request, 'startDate', $schedule->startDate);
            $request['frequency'] = $schedule->frequency;
            $request['duration'] = $mapDuration();
            $request['scheduleIdentifier'] = $schedule->id;
        } else { // Edit Fields
            if (!$schedule->hasStarted) {
                $request['scheduleIdentifier'] = $schedule->id;
                $request = $this->buildDate($request, 'startDate', $schedule->startDate, ($type === TransactionType::EDIT));
                $request['frequency'] = $schedule->frequency;
                $request['duration'] = $mapDuration();
            } else {
                $request = $this->buildDate($request, 'cancellationDate', $schedule->cancellationDate);
                $request = $this->buildDate($request, 'nextProcessingDate', $schedule->nextProcessingDate);
            }
        }

        return $request;
    }

    protected function buildDate($request, $name, $date = null, $force = false)
    {
        if (!empty($date) || $force) {
            if ($force && is_string($date)) {
                $request[$name] = $date;
            } else {
                $value = $date !== null ? $date->format('mdY') : null;
                $request[$name] = $value;
            }
        }
        return $request;
    }

    protected function buildAmount($request, $name, $amount, $currency, $type)
    {
        if ($amount !== null) {
            $node = [
                'value' => $amount * 100,
            ];

            if ($type === TransactionType::CREATE) {
                $node['currency'] = $currency;
            }

            $request[$name] = $node;
        }
        return $request;
    }

    protected function buildAddress($request, Address $address)
    {
        if ($address !== null) {
            $request['addressLine1'] = $address->streetAddress1;
            $request['addressLine2'] = $address->streetAddress2;
            $request['city'] = $address->city;
            $request['country'] = $address->country;
            $request['stateProvince'] = !empty($address->state) ? $address->state : $address->province;
            $request['zipPostalCode'] = $address->postalCode;
        }
        return $request;
    }
    #endregion

    #region Hydrate Entities
    protected function hydrateCustomer($response)
    {
        $customer = new Customer();
        $customer->key = isset($response->customerKey) ? $response->customerKey : null;
        $customer->id = isset($response->customerIdentifier) ? $response->customerIdentifier : null;
        $customer->firstName = isset($response->firstName) ? $response->firstName : null;
        $customer->lastName = isset($response->lastName) ? $response->lastName : null;
        $customer->company = isset($response->company) ? $response->company : null;
        $customer->status = isset($response->customerStatus) ? $response->customerStatus : null;
        $customer->title = isset($response->title) ? $response->title : null;
        $customer->department = isset($response->department) ? $response->department : null;
        $customer->email = isset($response->primaryEmail) ? $response->primaryEmail : null;
        $customer->homePhone = isset($response->phoneDay) ? $response->phoneDay : null;
        $customer->workPhone = isset($response->phoneEvening) ? $response->phoneEvening : null;
        $customer->mobilePhone = isset($response->phoneMobile) ? $response->phoneMobile : null;
        $customer->fax = isset($response->fax) ? $response->fax : null;
        $customer->address = new Address();
        $customer->address->streetAddress1 = isset($response->addressLine1) ? $response->addressLine1 : null;
        $customer->address->streetAddress2 = isset($response->addressLine2) ? $response->addressLine2 : null;
        $customer->address->city = isset($response->city) ? $response->city : null;
        $customer->address->state = isset($response->stateProvince) ? $response->stateProvince : null;
        $customer->address->postalCode = isset($response->zipPostalCode) ? $response->zipPostalCode : null;
        $customer->address->country = isset($response->country) ? $response->country : null;
        return $customer;
    }

    protected function hydratePaymentMethod($response)
    {
        $paymentMethod = new RecurringPaymentMethod();
        $paymentMethod->key = isset($response->paymentMethodKey) ? $response->paymentMethodKey : null;
        $paymentMethod->paymentType = isset($response->paymentMethodType) ? $response->paymentMethodType : null;
        $paymentMethod->preferredPayment = isset($response->preferredPayment) ? $response->preferredPayment : null;
        $paymentMethod->status = isset($response->paymentStatus) ? $response->paymentStatus : null;
        $paymentMethod->id = isset($response->paymentMethodIdentifier) ? $response->paymentMethodIdentifier : null;
        $paymentMethod->customerKey = isset($response->customerKey) ? $response->customerKey : null;
        $paymentMethod->nameOnAccount = isset($response->nameOnAccount) ? $response->nameOnAccount : null;
        $paymentMethod->commercialIndicator = isset($response->cpcInd) ? $response->cpcInd : null;
        $paymentMethod->taxType = isset($response->cpcTaxType) ? $response->cpcTaxType : null;
        $paymentMethod->expirationDate = isset($response->expirationDate) ? $response->expirationDate : null;
        $paymentMethod->address = new Address();
        $paymentMethod->address->streetAddress1 = isset($response->addressLine1) ? $response->addressLine1 : null;
        $paymentMethod->address->streetAddress2 = isset($response->addressLine2) ? $response->addressLine2 : null;
        $paymentMethod->address->city = isset($response->city) ? $response->city : null;
        $paymentMethod->address->state = isset($response->stateProvince) ? $response->stateProvince : null;
        $paymentMethod->address->postalCode= isset($response->zipPostalCode) ? $response->zipPostalCode : null;
        $paymentMethod->address->country = isset($response->country) ? $response->country : null;
        return $paymentMethod;
    }

    protected function hydrateSchedule($response)
    {
        $schedule = new Schedule();
        $schedule->key = isset($response->scheduleKey) ? $response->scheduleKey : null;
        $schedule->id = isset($response->scheduleIdentifier) ? $response->scheduleIdentifier : null;
        $schedule->customerKey = isset($response->customerKey) ? $response->customerKey : null;
        $schedule->name = isset($response->scheduleName) ? $response->scheduleName : null;
        $schedule->status = isset($response->scheduleStatus) ? $response->scheduleStatus : null;
        $schedule->paymentKey = isset($response->paymentMethodKey) ? $response->paymentMethodKey : null;
        if (isset($response->subtotalAmount)) {
            $subtotal = $response->subtotalAmount;
            $schedule->amount = intval($subtotal->value) / 100;
            $schedule->currency = $subtotal->currency;
        }
        if (isset($response->taxAmount)) {
            $taxAmount = $response->taxAmount;
            $schedule->taxAmount = intval($taxAmount->value) / 100;
        }
        $schedule->deviceId = isset($response->deviceID) ? $response->deviceID : null;
        $schedule->startDate = $response->startDate;
        $schedule->paymentSchedule = isset($response->processingDateInfo) ? $response->processingDateInfo : null;
        switch ($schedule->paymentSchedule) {
            case 'Last':
                $schedule->paymentSchedule = PaymentSchedule::LAST_DAY_OF_THE_MONTH;
                break;
            case 'First':
                $schedule->paymentSchedule = PaymentSchedule::FIRST_DAY_OF_THE_MONTH;
                break;
            default:
                $schedule->paymentSchedule = PaymentSchedule::DYNAMIC;
                break;
        }
        $schedule->frequency = isset($response->frequency) ? $response->frequency : null;
        $schedule->endDate = isset($response->endDate) ? $response->endDate : null;
        $schedule->reprocessingCount = isset($response->reprocessingCount) ? $response->reprocessingCount : null;
        $schedule->emailReceipt = isset($response->emailReceipt) ? $response->emailReceipt : null;
        $schedule->emailNotification = isset($response->emailAdvanceNotice) ? $response->emailAdvanceNotice : null;
        if ($schedule->emailNotification === null
            || $schedule->emailNotification === 'No'
        ) {
            $schedule->emailNotification = false;
        } else {
            $schedule->emailNotification = true;
        }
        // dept repay indicator
        $schedule->invoiceNumber = isset($response->invoiceNbr) ? $response->invoiceNbr : null;
        $schedule->poNumber = isset($response->poNumber) ? $response->poNumber : null;
        $schedule->description = isset($response->Description) ? $response->Description : null;
        // statusSetDate
        $schedule->nextProcessingDate = isset($response->nextProcessingDate)
            ? \DateTime::createFromFormat('mdY', $response->nextProcessingDate)
            : null;
        // previousProcessingDate
        // approvedTransactionCount
        // failureCount
        // totalApprovedAmountToDate
        // numberOfPaymentsRemaining
        $schedule->cancellationDate = isset($response->cancellationDate) ? $response->cancellationDate : null;
        // creationDate
        // lastChangeDate
        $schedule->hasStarted = isset($response->scheduleStarted) ? $response->scheduleStarted : null;
        return $schedule;
    }
    #endregion

    #region Validations

    /**
     * Tests the payment method for a token value
     *
     * @param IPaymentMethod $paymentMethod The payment method
     *
     * @return [bool, string|null]
     */
    protected function hasToken(IPaymentMethod $paymentMethod)
    {
        $tokenValue = null;

        if (!empty($paymentMethod->token)) {
            $tokenValue = $paymentMethod->token;
            return [true, $tokenValue];
        }

        return [false, $tokenValue];
    }

    #endregion

    protected function updateIntegrationHeader()
    {
        $pairs = array();

        foreach ($this->integrationHeader as $key => $value) {
            $pairs[] = sprintf('%s=%s', $key, $value);
        }
    
        $this->headers['HPS-Integration'] = implode(',', $pairs);
    }
}
