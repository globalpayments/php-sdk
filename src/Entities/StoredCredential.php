<?php

namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Entities\Enums\StoredCredentialInitiator;
use GlobalPayments\Api\Entities\Enums\StoredCredentialSequence;
use GlobalPayments\Api\Entities\Enums\StoredCredentialType;

class StoredCredential
{
    /** @var StoredCredentialType */
    public $type;
    /** @var StoredCredentialInitiator */
    public $initiator;
    /** @var StoredCredentialSequence */
    public $sequence;
    /** @var string */
    public $schemeId;
}
