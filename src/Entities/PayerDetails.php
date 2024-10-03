<?php

namespace GlobalPayments\Api\Entities;

class PayerDetails
{
    public ?string $id;
    public ?string $firstName;
    public ?string $lastName;
    public ?string $email;
    public ?Address $billingAddress;
    public ?Address $shippingAddress;
}