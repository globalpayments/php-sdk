<?php

namespace GlobalPayments\Api\Entities;

class TransferFundsAccountCollection extends \ArrayObject
{
    /**
     * @param FundsAccountDetails $transfer
     * @param string $id
     */
    public function add(FundsAccountDetails $transfer, string $id)
    {
        $this->offsetSet($id, $transfer);
    }

    public function get(string $id) : FundsAccountDetails
    {
        return $this->offsetGet($id);
    }
}