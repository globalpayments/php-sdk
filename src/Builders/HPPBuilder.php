<?php
//Hosted Payment Page builder
namespace GlobalPayments\Api\Builders;

use GlobalPayments\Api\Entities\{
    HPPData,
    PayerDetails,
    HPPOrder,
    HPPTransactionConfiguration,
    HPPPaymentMethodConfiguration,
    HPPAuthenticationConfiguration,
    HPPApmConfiguration,
    HPPNotifications,
    Address,
    HPPDisplayConfiguration,
    PhoneNumber
};
use GlobalPayments\Api\Entities\Enums\{
    PayByLinkType,
    CaptureMode,
    ChallengeRequestIndicator,
    HPPStorageModes,
    PaymentMethodUsageMode,
    ExemptStatus,
    HPPTypes,
    HPPFunctions,
    TransactionType
};
use GlobalPayments\Api\Entities\Exceptions\ArgumentException;
use GlobalPayments\Api\Services\HPPService;
use GlobalPayments\Api\Utils\StringUtils;


class HPPBuilder extends AuthorizationBuilder
{
    /**
     * @var HPPData
     */
    private $HPPData;
    /**
     * @var PayerDetails
     */
    private $payer;
    /**
     * @var HPPOrder
     */
    private $order;
    /**
     * @var HPPNotifications
     */
    private $notifications;
    /**
     * @var HPPTransactionConfiguration
     */
    private $transactionConfig;
    /**
     * @var HPPPaymentMethodConfiguration
     */
    private $paymentMethodConfig;
    /**
     * @var HPPAuthenticationConfiguration
     */
    private $authConfig;
    /**
     * @var HPPApmConfiguration
     */
    private $apmConfig;

    public function __construct()
    {
        parent::__construct(TransactionType::HOSTED_PAYMENT_PAGE);
        $this->HPPData = new HPPData();
        $this->payer = new PayerDetails();
        $this->order = new HPPOrder();
        $this->notifications = new HPPNotifications();
        $this->transactionConfig = new HPPTransactionConfiguration();
        $this->paymentMethodConfig = new HPPPaymentMethodConfiguration();
        $this->authConfig = new HPPAuthenticationConfiguration();
        $this->apmConfig = new HPPApmConfiguration();
    }

    /**
     * Set digital wallets for the payment method configuration
     *
     * @param array $providers Array of provider strings (e.g., ['googlepay', 'applepay'])
     * @return HPPBuilder this
     */
    public function withDigitalWallets(array $providers): self
    {
        $this->paymentMethodConfig->digitalWallets = [
            'provider' => $providers
        ];
        return $this;
    }

    /**
     * Create a new instaance of itself
     *
     * @return HPPBuilder this
     */
    public static function create(): HPPBuilder
    {
        return new self();
    }

    /**
     * Set payment page name
     * 
     * @param string $name
     * @return HPPBuilder
     */
    public function withName(string $name): self
    {
        $this->HPPData->name = $name;
        return $this;
    }

    /**
    *  Set description for the hosted payment page, shown on real controll
    *
    * @param string $description
    * @return HPPBuilder this
    */
    public function withDescription($description): self
    {
        $this->HPPData->description = $description;
        return $this;
    }

    /**
     * Set reference for the hosted payment page
     *
     * @param string $reference
     * @return HPPBuilder this
     */
    public function withReference(string $reference): self
    {
        $this->HPPData->reference = $reference;
        return $this;
    }

    /**
     *  Set expiration date for the payment link
     * 
     * @param string $expirationDate
     * @return HPPBuilder this
     */ 
    public function withExpirationDate(string $expirationDate): self
    {
        $this->HPPData->expirationDate = $expirationDate;
        return $this;
    }

    /**
     * Add an image to the hosted payment page, not currently supported
     *
     * @param string $base64Content
     * @throws ArgumentException When the base64 content is empty or invalid
     * @return HPPBuilder this
     */
    
    public function withImage(string $base64Content): self
    {
        if (empty($base64Content)) {
            throw new ArgumentException('Base64 image content is required');
        }
        if (!preg_match('/^[A-Za-z0-9+\/]*={0,2}$/', $base64Content)) {
            throw new ArgumentException('Invalid Base64 format');
        }
        $this->HPPData->images = [
            'b64_content' => $base64Content
        ];
        return $this;
    }

    /** 
     * Sets the payer information for the hosted payment request
     * 
     * @param PayerDetails $payer
     * @return HPPBuilder this
     * 
     */
    public function withPayer(PayerDetails $payer): self
    {

        if (empty($payer->firstName) || empty($payer->lastName) || empty($payer->email)) {
            throw new ArgumentException('First name, last name, and email are required');
        }
        if (!filter_var($payer->email, FILTER_VALIDATE_EMAIL)) {
            throw new ArgumentException('Invalid email format');
        }
        if (!in_array($payer->status, ['NEW', 'ACTIVE'])) {
            throw new ArgumentException('Payer status must be either "NEW" or "ACTIVE"');
        }
        if ($payer->status === 'NEW' && !empty($payer->id)) {
            throw new ArgumentException('Payer ID should not be provided when status is "NEW"');
        }
        $this->payer = $payer;
        if(!property_exists($payer, "name")){
            $this->payer->name = $payer->firstName . ' ' . $payer->lastName;
        }
        return $this;
    }
    /**
     * Add phone number to the payer object
     * 
     * @param PhoneNumber $phone
     * @return HPPBuilder this
     */
    public function withPayerPhone(PhoneNumber $phone): self
    {
        $this->payer->mobilePhone = $phone;
        return $this;
    }

    /** 
     * Sets the billing address information for the hosted payment request
     * 
     * @param Address $address The billing address object
     * @return HPPBuilder this
     */
    public function withBillingAddress(Address $address): self
    {
        $this->payer->billingAddress = $address;
        return $this;
    }
    
    /** 
     * Sets the shipping address information for the hosted payment request
     * 
     * @param Address $address The shipping address object
     * @return HPPBuilder this
     */
    public function withShippingAddress(Address $address): self
    {
        $this->order->shippingAddress = $address;
        return $this;
    }

    /**
     * Sets the shipping phone number for the hosted payment request
     * 
     * @param PhoneNumber $phone The shipping phone number object
     * @return HPPBuilder this
     */
    public function withShippingPhone(PhoneNumber $phone): self
    {
        $this->order->shippingPhone = $phone;
        return $this;
    }

    /**
     * Indicates whether the billing and shipping addresses are the same
     * 
     * @param bool $indicator true if the addresses match, false if they do not
     * @return HPPBuilder this
     */
    public function withAddressMatchIndicator($indicator): self
    {
        $this->payer->addressMatchIndicator = StringUtils::boolToYesNo($indicator);
        return $this;
    }    /**
     * Configure order amount 
     
     * @param string $amount
     * @throws ArgumentException When the amount is not a number or its a negative number
     * @return HPPBuilder this
     */
    public function withAmount( $amount ): self
    {
        if (!is_numeric($amount) || floatval($amount) <= 0) {
            throw new ArgumentException('Issue with the amount, it must be a positive number');
        }
     
        $this->order->amount = $amount;
        return $this;
    }

    /**
     * Set the currency for the hosted payment
     * 
     * @param string $currency The ISO 4217 3-character currency code (e.g., 'USD', 'EUR', 'GBP')
     * @throws ArgumentException When the currency code is not 3 characters
     * @return HPPBuilder this
     */
    public function withCurrency( $currency ): self
    {
        if (strlen($currency) !== 3) {
            throw new ArgumentException('Currency must be a 3-character code');
        }
        $this->order->currency = strtoupper($currency);
        return $this;
    }
   
    /**
     * Add an order reference to the order
     * 
     * @param string $reference
     * @return HPPBuilder this
     */
    public function withOrderReference(string $reference): self
    {
        $this->order->reference = $reference;
        return $this;
    }

    /**
     * Configure transaction settings
     * 
     * @param string $channel
     * @param string $country
     * @param CaptureMode|string $captureMode
     * @param array $allowedPaymentMethods
     * @param PaymentMethodUsageMode|string $usageMode
     * @param string $usageLimit
     * @return HPPBuilder this
     */
    public function withTransactionConfig(
        string $channel = 'CNP',
        string $country = 'GB',
        CaptureMode|string $captureMode = CaptureMode::AUTO,
        array $allowedPaymentMethods = ['CARD'],
        PaymentMethodUsageMode|string $usageMode = PaymentMethodUsageMode::SINGLE,
        string $usageLimit = '1'
    ): self
    {
        $this->transactionConfig->channel = $channel;
        $this->transactionConfig->country = $country;
        // captureMode does not extend Enum, validate it manulay
        if (is_string($captureMode)) {
            // Check if it's a valid CaptureMode constant
            $reflection = new \ReflectionClass(CaptureMode::class);
            $constants = $reflection->getConstants();
                if (!in_array($captureMode, $constants)) {
                    throw new ArgumentException("Invalid CaptureMode value: {$captureMode}");
                }
        }
        $this->transactionConfig->captureMode = $captureMode;
        $this->transactionConfig->allowedPaymentMethods = $allowedPaymentMethods;
        $this->transactionConfig->usageMode = PaymentMethodUsageMode::validate($usageMode);
        $this->transactionConfig->usageLimit = $usageLimit;
        return $this;
    }

    /**
     * Configure currency conversion mode
     * 
     * @param bool $currencyConversionMode true to enable, false to disable
     * @return HPPBuilder this
     */
    public function withCurrencyConversionMode($currencyConversionMode): self
    {
        $this->transactionConfig->currencyConversionMode = $currencyConversionMode;
        return $this;
    }

    /**
     * Configure authentication settings
     * 
     * @param ChallengeRequestIndicator|string $preference
     * @param ExemptStatus|string $exemptStatus
     * @param bool $billingAddressRequired
     * @throws ArgumentException When the preference, exempt status, or billing address required is not a valid enum value
     * @return HPPBuilder this
     */
    public function withAuthentication(
        ChallengeRequestIndicator|string $preference = ChallengeRequestIndicator::CHALLENGE_PREFERRED,
        ExemptStatus|string $exemptStatus = ExemptStatus::LOW_VALUE,
        $billingAddressRequired = true
    ): self
    {
        $this->authConfig->preference = ChallengeRequestIndicator::validate($preference);
        $this->authConfig->exemptStatus = ExemptStatus::validate($exemptStatus);
        $this->authConfig->billingAddressRequired = $billingAddressRequired;
        return $this;
    }

    /**
     * Change the payment method storage mode, this indecates weather to prompt the user to save their payment method
     * for future use
     * 
     * @param HPPStorageModes|string $storageMode
     * @return HPPBuilder this
     */
    public function withPaymentMethodConfig(HPPStorageModes|string $storageMode = HPPStorageModes::PROMPT): self
    {
        $this->paymentMethodConfig->storageMode = HPPStorageModes::validate($storageMode);
        return $this;
    }
    
    /**
     * Configure AMP settings, these are specific to PayPal, if passed on a non-PayPal transaction, they will be ignored
     *
     * @param bool $shippingAddressEnabled Whether shipping address passing will be activated for PayPal
     * @param bool $addressOverride Whether the shipping address can be changed by the customer on PayPal review page
     * @throws ArgumentException When parameters are not boolean values
     * @return HPPBuilder this
     * 
     */
    public function withApm($shippingAddressEnabled = true, $addressOverride = true): self
    {
        // Validate parameters are boolean values
        if (!is_bool($shippingAddressEnabled) || !is_bool($addressOverride)) {
            throw new ArgumentException(
                'Shipping address enabled and address override must be boolean values'
            );
        }
        $this->apmConfig->shippingAddressEnabled = StringUtils::boolToYesNo($shippingAddressEnabled);
        $this->apmConfig->addressOverride = StringUtils::boolToYesNo($addressOverride);
        return $this;
    }
   
    /**
     * This will add a shipping charge to the hosted payment page
     * 
     * @param bool $shippable Whether shipping is chargeable
     * @param string|null $shippingAmount The shipping amount (required when $shippable is true)
     * @throws ArgumentException When invalid params are provided or
     * When shippable is true but no shipping amount is provided, or if the shipping amount is not a positive number
     * @return HPPBuilder this
     */
    public function withShipping($shippable = false, ?string $shippingAmount = null): self
    {
        if (!is_bool($shippable)) {
            throw new ArgumentException('Shippable must be a boolean value');
        }

        $this->HPPData->shippable = StringUtils::boolToYesNo($shippable);
        if ($shippable === true && $shippingAmount !== null) {
            if (!is_numeric($shippingAmount) || floatval($shippingAmount) < 0) {
                throw new ArgumentException('Shipping amount must be a positive number');
            }
            $this->HPPData->shippingAmount = $shippingAmount;
        }
        return $this;
    }

    /**
     * Build the hosted payment page
     * 
     * @param string $configName
     * @throws ArgumentException When validation fails or when required fields are missing
     * @return HPPData
     */
    public function build(): HPPData
    {
        $errors = $this->validate();
        if (!empty($errors)) {
            throw new ArgumentException('Validation failed: ' . implode(', ', $errors));
        }
        $this->paymentMethodConfig->authentications = $this->authConfig;
        $this->paymentMethodConfig->apm = $this->apmConfig;
        $this->order->HPPTransactionConfiguration = $this->transactionConfig;
        $this->order->HPPPaymentMethodConfiguration = $this->paymentMethodConfig;
        $this->HPPData->type = PayByLinkType::HOSTED_PAYMENT_PAGE;
        $this->HPPData->payer = $this->payer;
        $this->HPPData->order = $this->order;
        $this->HPPData->notifications = $this->notifications;
        return $this->HPPData;
    }

    /**
     * Execute the hosted payment page request
     * @param string $configName 
     * @throws ArgumentException When validation fails or when required fields are missing
     * @return PayByLinkResponse Containing the HPP URL
     */
    public function execute($configName = 'default')
    {
        $data = $this->build();
        return HPPService::create($data)->execute($configName);
    }

    /**
     * Set the type of hosted payment page
     *
     * @param HPPTypes|string $type 
     * @throws ArgumentException When the type is not a valid or not a HPPTypes enum value
     * @return HPPBuilder this
     */
    public function withType(HPPTypes|string $type): self
    {
        $this->HPPData->type = HPPTypes::validate($type);
        return $this;
    }
    
    /**
     * Set the function in the hosted payment page request. 
     *
     * @param HPPFunctions|string $function 
     * @throws ArgumentException When the function parameter is not a valid or not a HPPFunctions enum value
     * @return HPPBuilder this
     */
    public function withFunction(HPPFunctions|string $function): self
    {
        $this->HPPData->function = HPPFunctions::validate($function);
        return $this;
    }
    
    /**
     * Set display configuration for the hosted payment page iframe, this functionality will be in a future release
     * Note the comments from the params are taken straight from the HPP documentation
     *
     * @param string $iframeDimensionsDomain This field must contain the domain of the application hosting the iFrame
     * or WebView. This will tell the HPP to post back the height and width any time it changes, for example if an
     * input warning is displayed.
     * @param string $iframeResponseDomain This field must contain the domain of the application hosting the iFrame or
     * WebView. This will tell the HPP to post back the transaction response to the parent frame or window. The
     * response is posted back as a name/value pair JSON string with the values Base64 encoded. (string)
     * @return HPPBuilder this
     */
    public function withHPPDisplayConfiguration(string $iframeDimensionsDomain, ?string $iframeResponseDomain = null): self
    {
        $displayConfig = new HPPDisplayConfiguration($iframeDimensionsDomain, $iframeResponseDomain);
        $this->HPPData->HPPDisplayConfiguration = $displayConfig->toArray();
        return $this;
    }
    
    /**
     * Endpoints for the Hosted Payment Page 
     *
     * @param string $returnUrl URL to display after payment is completed, should create a form to submit the payment details to the final URL
     * @param string $statusUrl URL for receiving status updates, when certain events occur
     * @param string $cancelUrl URL to redirect to if payment is cancelled
     * @throws ArgumentException When any of the URLs are invaild
     * @return HPPBuilder this
     */
    public function withNotifications(string $returnUrl, string $statusUrl, string $cancelUrl = ""): self
    {
        if (!filter_var($returnUrl, FILTER_VALIDATE_URL) || 
            !filter_var($statusUrl, FILTER_VALIDATE_URL) || 
            (!empty($cancelUrl) && !filter_var($cancelUrl, FILTER_VALIDATE_URL))) {
            throw new ArgumentException('Invalid URL format for notifications');
        }

        $this->notifications->returnUrl = $returnUrl;
        $this->notifications->statusUrl = $statusUrl;
        $this->notifications->cancelUrl = $cancelUrl;
        return $this;
    }
    
    /**
     * Set app email only used for the EXCHANGE_APP_CREDENTIALS type
     *
     * @param string $appEmail Email where app credentials should be sent
     * @throws ArgumentException When the email format is invalid
     * @return HPPBuilder this
     */
    public function withAppEmail(string $appEmail): self
    {
        if (!filter_var($appEmail, FILTER_VALIDATE_EMAIL)) {
            throw new ArgumentException('Invalid email format');
        }
        $this->HPPData->appEmail = $appEmail;
        return $this;
    }
    
    /**
     * Indicates the apps you want the credentials for, only used in EXCHANGE_APP_CREDENTIALS type
     *
     * @param array $appIds
     * @return HPPBuilder this
     */
    public function withAppIds(array $appIds): self
    {
        $this->HPPData->appIds = $appIds;
        return $this;
    }
    
    /**
     * Set URL of the page requesting the hosted payment page
     *
     * @param string $referrerUrl
     * @throws ArgumentException When the URL format is invalid
     * @return HPPBuilder this
     */
    public function withReferrerUrl(string $referrerUrl): self
    {
        if (!filter_var($referrerUrl, FILTER_VALIDATE_URL)) {
            throw new ArgumentException('Invalid URL format for referrer URL');
        }
        $this->HPPData->referrerUrl = $referrerUrl;
        return $this;
    }
    
    /**
     * Set the IP address of the page that will host the third-party payment page, not used in HPP
     *
     * @param string $ipAddress
     * @param string|null $ipSubnetMask
     * @throws ArgumentException When the IP address or subnet mask format is invalid
     * @return HPPBuilder this
     */
    public function withIpAddress(string $ipAddress, ?string $ipSubnetMask = null): self
    {
        if (!filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            throw new ArgumentException('Invalid IP address format');
        }
        
        $this->HPPData->ipAddress = $ipAddress;
        
        if ($ipSubnetMask !== null) {
            if (!filter_var($ipSubnetMask, FILTER_VALIDATE_IP)) {
                throw new ArgumentException('Invalid IP subnet mask format');
            }
            $this->HPPData->ipSubnetMask = $ipSubnetMask;
        }
        
        return $this;
    }

    /**
     * Validate the current configuration, returning an array of errors if any are found
     * @return errors array
     */
    public function validate(): array
    {
        $errors = [];
        if (empty($this->HPPData->name)) {
            $errors[] = 'Name is required';
        }
        if (empty($this->order->amount)) {
            $errors[] = 'Amount is required';
        }
        if (empty($this->payer->email)) {
            $errors[] = 'Payer email is required';
        }
        $errors = array_merge($errors, $this->transactionConfig->validate());
        $errors = array_merge($errors, $this->paymentMethodConfig->validate());
        return $errors;
    }
}
