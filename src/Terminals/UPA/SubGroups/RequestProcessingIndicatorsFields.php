<?php

namespace GlobalPayments\Api\Terminals\UPA\SubGroups;

use GlobalPayments\Api\Entities\Enums\GatewayProvider;
use GlobalPayments\Api\Mapping\EnumMapping;
use GlobalPayments\Api\Terminals\Abstractions\IRequestSubGroup;
use GlobalPayments\Api\Terminals\Builders\TerminalAuthBuilder;
use GlobalPayments\Api\Terminals\Builders\TerminalBuilder;

class RequestProcessingIndicatorsFields implements IRequestSubGroup
{
    /** @var bool Indicates whether the transaction is Quick Chip or Traditional EMV. */
    public ?string $quickChip;
    /** @var bool Flag indicating whether LUHN check will be performed on the account number. */
    public string $checkLuhn;

    /** @var bool Indicates whether the CVV should be prompted. */
    public string $securityCode;
    /** @var string This indicates whether the card entered falls under the card BIN range of the card type provided */
    public string $cardTypeFilter;

    public function setParams(TerminalBuilder $builder)
    {
        /** @var TerminalAuthBuilder $builder */
        if (isset($builder->isQuickChip)) {
            $this->quickChip = $builder->isQuickChip === true ? 'Y' : 'N';
        }
        if (isset($builder->hasCheckLuhn)) {
            $this->checkLuhn = $builder->hasCheckLuhn === true ? 'Y' : 'N';
        }
        if (isset($builder->hasSecurityCode)) {
            $this->securityCode = $builder->hasSecurityCode === true ? 'Y' : 'N';
        }
        if (!empty($builder->cardTypeFilter)) {
            $cardTypeFilter = '';
            array_walk($builder->cardTypeFilter, function ($v) use (&$cardTypeFilter) {
                $cardTypeFilter .= EnumMapping::mapCardType(GatewayProvider::UPA, $v) . '|';
            });
            $this->cardTypeFilter = rtrim($cardTypeFilter, '|');
        }
    }

    public function getElementString()
    {
        return array_filter((array) $this, function ($val) {
            return !is_null($val);
        });
    }
}