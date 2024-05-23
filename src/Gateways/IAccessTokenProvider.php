<?php

namespace GlobalPayments\Api\Gateways;
use GlobalPayments\Api\Entities\GpApi\GpApiRequest;

interface IAccessTokenProvider
{
    public function signIn($appId, $appKey, $secondsToExpire = null, $intervalToExpire = null, $permissions = []) : GpApiRequest;
    public function singOut() : GpApiRequest;
}