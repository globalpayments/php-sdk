<?php

namespace GlobalPayments\Api\Entities\GpApi;

class AccessTokenRequest
{
    public $app_id;
    public $nonce;
    public $secret;
    public $grant_type;
    public $seconds_to_expire;
    public $interval_to_expire;
    public $permissions;

    /**
     * AccessTokenRequest constructor.
     * @param $app_id
     * @param $nonce
     * @param $secret
     * @param $grant_type
     * @param $seconds_to_expire
     * @param $interval_to_expire
     */
    public function __construct($app_id, $nonce, $secret, $grant_type, $seconds_to_expire, $interval_to_expire, $permissions)
    {
        $this->app_id = $app_id;
        $this->nonce = $nonce;
        $this->secret = $secret;
        $this->grant_type = $grant_type;
        $this->seconds_to_expire = $seconds_to_expire;
        $this->interval_to_expire = $interval_to_expire;
        $this->permissions = $permissions;
    }
}