<?php

namespace GlobalPayments\Api\Terminals\Interfaces;

interface IRequestIdProvider
{
    /*
     * Request Id is mandatory. It's a 1-12 digit integer used to uniquely identify the request.
     * Echoed back by the device in the response
     *
     * It can be any random number or order number or invoice number or transaction id
     */

    public function getRequestId();
}
