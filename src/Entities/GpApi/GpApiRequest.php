<?php


namespace GlobalPayments\Api\Entities\GpApi;


class GpApiRequest
{
    public $account_name;

    public $reference;

    public $channel;

    public $country;

    public static function initBaseParams(&$request, $config)
    {
        $request->account_name = $config->getAccessTokenInfo()->getTransactionProcessingAccountName();
        $request->channel = $config->getChannel();
        $request->country = $config->getCountry();
    }
}