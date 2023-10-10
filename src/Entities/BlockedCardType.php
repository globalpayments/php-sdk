<?php

namespace GlobalPayments\Api\Entities;

class BlockedCardType
{
    public bool $consumerdebit;
    public bool $consumercredit;
    public bool $commercialcredit;
    public bool $commercialdebit;
}