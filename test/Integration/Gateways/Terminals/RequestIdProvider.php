<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\Terminals;

use GlobalPayments\Api\Terminals\Interfaces\IRequestIdProvider;

class RequestIdProvider implements IRequestIdProvider
{

    public function getRequestId()
    {
        return 10000 + random_int(0, 99999);
    }
}
