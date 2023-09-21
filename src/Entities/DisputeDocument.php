<?php

namespace GlobalPayments\Api\Entities;

class DisputeDocument extends Document
{
    /** @var string */
    public $type;

    /** @var string */
    public $b64_content;
}