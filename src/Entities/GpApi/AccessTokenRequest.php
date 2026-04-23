<?php

declare(strict_types=1);

namespace GlobalPayments\Api\Entities\GpApi;

class AccessTokenRequest implements \JsonSerializable
{
    public $app_id;
    public $nonce;
    public $secret;
    public $grant_type;
    public $seconds_to_expire;

    /**
     * Masks the account, merchant and app details that is returned in the response to a tokenisation request.
     * 
     * YES - A restricted access token will be created. Restricted access token is used when integrating
     * with Drop-in UI or Hosted Fields to create a single-use payment token.
     * 
     * NO - A restricted access token will not be created.
     * 
     * @var ?string
     */
    public ?string $restricted_token;

    public $interval_to_expire;
    public $permissions;
    public $credentials;

     public function __construct(
        $app_id,
        $nonce,
        $secret,
        $grant_type,
        $seconds_to_expire,
        $interval_to_expire,
        $permissions,
        $credentials = null,
        ?string $restricted_token = null
    )
    {
        $this->app_id = $app_id;
        $this->nonce = $nonce;
        $this->secret = $secret;
        $this->grant_type = $grant_type;
        $this->seconds_to_expire = $seconds_to_expire;
        $this->interval_to_expire = $interval_to_expire;
        $this->permissions = $permissions;
        $this->credentials = $credentials;
        $this->restricted_token = $restricted_token;
    }

    public function jsonSerialize(): array
    {
        $data = [];
        
        if ($this->app_id !== null) {
            $data['app_id'] = $this->app_id;
        }
        if ($this->nonce !== null) {
            $data['nonce'] = $this->nonce;
        }
        if ($this->secret !== null) {
            $data['secret'] = $this->secret;
        }
        
        $data['grant_type'] = $this->grant_type;
        
        if ($this->seconds_to_expire !== null) {
            $data['seconds_to_expire'] = $this->seconds_to_expire;
        }
        if ($this->interval_to_expire !== null) {
            $data['interval_to_expire'] = $this->interval_to_expire;
        }
        if (!empty($this->permissions)) {
            $data['permissions'] = $this->permissions;
        }
        
        if (!empty($this->credentials)) {
            $data['credentials'] = $this->credentials;
        }

        if (!empty($this->restricted_token)) {
            $data['restricted_token'] = $this->restricted_token;
        }
        
        return $data;
    }
}