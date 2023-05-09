<?php

namespace GlobalPayments\Api\Entities;

class TransferFundsAccountCollection extends \ArrayObject
{
    /**
     * @param TransferFundsAccountDetails $transfer
     * @param string $id
     */
    public function add(TransferFundsAccountDetails $transfer, string $id)
    {
        $this->offsetSet($id, $transfer);
    }

    public function get(string $id) : TransferFundsAccountDetails
    {
        return $this->offsetGet($id);
    }
}