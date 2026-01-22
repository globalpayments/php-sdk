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
    public ?bool $cardCaptureCapability = null;

    /**
     * Used w/TransIT
     *
     * @var CardDataInputCapability
     */
    public mixed $cardDataInputCapability = null;

    /**
     * Used w/TransIT
     *
     * @var CardDataOutputCapability
     */
    public mixed $cardDataOutputCapability = null;

    /**
     * Used w/TransIT; corresponding tag will default to eComm or Manual if this isn't used
     *
     * @var CardDataSource
     */
    public mixed $cardDataSource = null;

    /**
     * Used w/TransIT
     *
     * @var CardHolderAuthenticationCapability
     */
    public mixed $cardHolderAuthenticationCapability = null;

    /**
     * Used w/TransIT
     *
     * @var CardHolderAuthenticationEntity
     */
    public mixed $cardHolderAuthenticationEntity = null;

    /**
     * Used w/TransIT
     *
     * @var OperatingEnvironment
     */
    public mixed $operatingEnvironment = null;

    /**
     * Used w/TransIT
     *
     * @var PinCaptureCapability
     */
    public mixed $pinCaptureCapability = null;

    /**
     * Used w/TransIT
     *
     * @var TerminalOutputCapability
     */
    public mixed $terminalOutputCapability = null;

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
