<?php

declare(strict_types=1);

namespace GlobalPayments\Api\Entities\GpApi;

use GlobalPayments\Api\Gateways\IAccessTokenProvider;

class GpApiSessionInfo implements IAccessTokenProvider
{
    private static function generateSecret(string $nonce, string $appKey): string
    {
        return hash('SHA512', $nonce . $appKey);
    }

    private static function generateNonce(): string
    {
        return (new \DateTime())->format(\DateTime::RFC3339);
    }

    public function signIn(
        $appId,
        $appKey,
        $secondsToExpire = null,
        $intervalToExpire = null,
        $permissions = [],
        $porticoCredentials = null,
        $secretApiKey = null
    ): GpApiRequest
    {
        $nonce = self::generateNonce();
        $credentials = null;

        // Build credentials array if Portico credentials are provided
        if (!empty($porticoCredentials)) {
            $credentials = [];
            
            if (!empty($porticoCredentials['deviceId'])) {
                $credentials[] = ['name' => 'device_id', 'value' => $porticoCredentials['deviceId']];
            }
            if (!empty($porticoCredentials['siteId'])) {
                $credentials[] = ['name' => 'site_id', 'value' => $porticoCredentials['siteId']];
            }
            if (!empty($porticoCredentials['licenseId'])) {
                $credentials[] = ['name' => 'license_id', 'value' => $porticoCredentials['licenseId']];
            }
            if (!empty($porticoCredentials['username'])) {
                $credentials[] = ['name' => 'username', 'value' => $porticoCredentials['username']];
            }
            if (!empty($porticoCredentials['password'])) {
                $credentials[] = ['name' => 'password', 'value' => $porticoCredentials['password']];
            }
            
            if (!empty($appKey)) {
                $credentials[] = ['name' => 'apikey', 'value' => $appKey];
            }
        }
        
        if (!empty($secretApiKey)) {
            if (empty($credentials)) {
                $credentials = [];
            }
            $credentials[] = ['name' => 'apikey', 'value' => $secretApiKey];
        }

        $finalAppId = null;
        $finalNonce = null;
        $finalSecret = null;
        
        if (!empty($appId) && !empty($appKey)) {
            $finalAppId = $appId;
            $finalNonce = $nonce;
            $finalSecret = self::generateSecret($nonce, $appKey);
        }

        $requestBody = new AccessTokenRequest(
            $finalAppId,
            $finalNonce,
            $finalSecret,
            'client_credentials',
            $secondsToExpire,
            $intervalToExpire,
            $permissions,
            $credentials
        );

        return new GpApiRequest(GpApiRequest::ACCESS_TOKEN_ENDPOINT, 'POST', $requestBody);
    }

    public function singOut(): GpApiRequest
    {
        throw new \BadMethodCallException('Method not implemented');
    }
}