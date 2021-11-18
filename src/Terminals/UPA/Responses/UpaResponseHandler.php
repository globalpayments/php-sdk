<?php

namespace GlobalPayments\Api\Terminals\UPA\Responses;

use GlobalPayments\Api\Terminals\Interfaces\IDeviceResponseHandler;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;

class UpaResponseHandler implements IDeviceResponseHandler
{

    
    public function mapResponse($messageReader = null)
    {
    }

    public function checkResponse($commandResult)
    {
        if (!empty($commandResult['result']) && $commandResult['result'] === 'Failed') {
            throw new GatewayException(
                sprintf(
                    'Unexpected Gateway Response: %s - %s',
                    $commandResult['errorCode'],
                    $commandResult['errorMessage']
                ),
                $commandResult['errorCode'],
                $commandResult['errorMessage']
            );
        }
    }
}
