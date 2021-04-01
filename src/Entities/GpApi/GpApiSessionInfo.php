<?php

namespace GlobalPayments\Api\Entities\GpApi;

class GpApiSessionInfo
{
    private static function generateSecret($nonce, $appKey)
    {
        return hash('SHA512', $nonce . $appKey);
    }

    private static function generateNonce()
    {
        $base = new \DateTime();
        return $base->format(\DateTime::RFC3339);
    }

    public static function signIn($appId, $appKey, $secondsToExpire = null, $intervalToExpire = null, $permissions = [])
    {
        $nonce = self::generateNonce();

        $requestBody = new AccessTokenRequest(
            $appId,
            $nonce,
            self::generateSecret($nonce, $appKey),
            'client_credentials',
            $secondsToExpire,
            $intervalToExpire,
            $permissions
        );

        return new GpApiRequest(GpApiRequest::ACCESS_TOKEN_ENDPOINT, 'POST', $requestBody);
    }
}