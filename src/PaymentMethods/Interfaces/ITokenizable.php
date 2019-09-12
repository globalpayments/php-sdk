<?php

namespace GlobalPayments\Api\PaymentMethods\Interfaces;

interface ITokenizable
{
    public function tokenize();
    public function updateTokenExpiry();
    public function deleteToken();
}
