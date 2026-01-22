<?php

namespace GlobalPayments\Api\Entities\BillPay;

use DateTime;

class TokenData
{
    private DateTime $lastUsedDateUTC;
    private bool $isExpired;
    private bool $sharedTokenWithGroup;
    private ?array $merchants;

    public function __construct()
    {
        $this->merchants = array();
    }
    
    public function getLastUsedDateUTC(): DateTime
    {
        return $this->lastUsedDateUTC;
    }

    public function setLastUsedDateUTC(DateTime $lastUsedDateUTC): void
    {
        $this->lastUsedDateUTC = $lastUsedDateUTC;
    }

    public function isExpired(): bool
    {
        return $this->isExpired;
    }

    public function setExpired(bool $isExpired): bool
    {
        return $this->isExpired = $isExpired;
    }

    public function isSharedTokenWithGroup(): bool
    {
        return $this->sharedTokenWithGroup;
    }

    public function setSharedTokenWithGroup(bool $sharedTokenWithGroup): bool
    {
        return $this->sharedTokenWithGroup = $sharedTokenWithGroup;
    }

    /** @return array<String> */
    public function getMerchants(): array
    {
        return $this->merchants;
    }

    public function setMerchants(?array $merchants): void
    {
        $this->merchants = $merchants;
    }

}

