<?php


namespace GlobalPayments\Api\Entities\GpApi;

class AccessToken
{
    public $token;
    public $type;
    public $time_created;
    public $seconds_to_expire;

    /**
     * AccessToken constructor.
     * @param $token
     * @param $type
     * @param $time_created
     * @param $seconds_to_expire
     */
    public function __construct($token, $type, $time_created, $seconds_to_expire)
    {
        $this->token = $token;
        $this->type = $type;
        $this->time_created = $time_created;
        $this->seconds_to_expire = $seconds_to_expire;
    }


    public function composeAuthorizationHeader()
    {
        return $this->type . ' ' . $this->token;
    }
}
