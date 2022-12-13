<?php

namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Entities\Enums\CustomerDocumentType;

class CustomerDocument
{
    /** @var string */
    public $reference;

    /** @var string */
    public $issuer;

    /** @var CustomerDocumentType */
    public $type;

    public function __construct($reference, $issuer, $type)
    {
        $this->reference = $reference;
        $this->issuer = $issuer;
        $this->type = $type;
    }
}