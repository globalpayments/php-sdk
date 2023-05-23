<?php

namespace GlobalPayments\Api\Terminals\UPA\Responses;

use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Terminals\TerminalResponse;

class UpaResponseHandler extends TerminalResponse
{
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
