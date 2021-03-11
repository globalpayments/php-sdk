<?php

namespace GlobalPayments\Api\ServiceConfigs;

use GlobalPayments\Api\Entities\Enums\CardHolderAuthenticationEntity;
use GlobalPayments\Api\Entities\Enums\CardDataInputCapability;
use GlobalPayments\Api\Entities\Enums\CardDataOutputCapability;
use GlobalPayments\Api\Entities\Enums\CardHolderAuthenticationCapability;
use GlobalPayments\Api\Entities\Enums\OperatingEnvironment;
use GlobalPayments\Api\Entities\Enums\PinCaptureCapability;
use GlobalPayments\Api\Entities\Enums\TerminalOutputCapability;

class AcceptorConfig
{

    /**
     * Used w/TransIT
     *
     * @var bool
     */
    public $cardCaptureCapability;

    /**
     * Used w/TransIT
     *
     * @var CardDataInputCapability
     */
    public $cardDataInputCapability;

    /**
     * Used w/TransIT
     *
     * @var CardDataOutputCapability
     */
    public $cardDataOutputCapability;

    /**
     * Used w/TransIT; corresponding tag will default to eComm or Manual if this isn't used
     *
     * @var CardDataSource
     */
    public $cardDataSource;

    /**
     * Used w/TransIT
     *
     * @var CardHolderAuthenticationCapability
     */
    public $cardHolderAuthenticationCapability;

    /**
     * Used w/TransIT
     *
     * @var CardHolderAuthenticationEntity
     */
    public $cardHolderAuthenticationEntity;

    /**
     * Used w/TransIT
     *
     * @var OperatingEnvironment
     */
    public $operatingEnvironment;

    /**
     * Used w/TransIT
     *
     * @var PinCaptureCapability
     */
    public $pinCaptureCapability;

    /**
     * Used w/TransIT
     *
     * @var TerminalOutputCapability
     */
    public $terminalOutputCapability;

    public function __construct(
        $cardCaptureCapability = false,
        $cardDataInputCapability = CardDataInputCapability::KEYED_ENTRY_ONLY,
        $cardDataOutputCapability = CardDataOutputCapability::NONE,
        $cardHolderAuthenticationCapability = CardHolderAuthenticationCapability::NO_CAPABILITY,
        $cardHolderAuthenticationEntity = CardHolderAuthenticationEntity::NOT_AUTHENTICATED,
        $operatingEnvironment = OperatingEnvironment::OFF_MERCHANT_PREMISES_UNATTENDED,
        $pinCaptureCapability = PinCaptureCapability::NONE,
        $terminalOutputCapability = TerminalOutputCapability::DISPLAY_ONLY
    ) {
        $this->cardCaptureCapability                = $cardCaptureCapability;
        $this->cardDataInputCapability              = $cardDataInputCapability;
        $this->cardDataOutputCapability             = $cardDataOutputCapability;
        $this->cardHolderAuthenticationCapability   = $cardHolderAuthenticationCapability;
        $this->cardHolderAuthenticationEntity       = $cardHolderAuthenticationEntity;
        $this->operatingEnvironment                 = $operatingEnvironment;
        $this->pinCaptureCapability                 = $pinCaptureCapability;
        $this->terminalOutputCapability             = $terminalOutputCapability;
    }

    public function validate()
    {
        // for use in future gateway integrations
    }
}
