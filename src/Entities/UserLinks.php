<?php

namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Entities\Enums\UserLevelRelationship;


class UserLinks
{
    /**
     * Describes the relationship the associated link href value has to the current resource.
     *
     * @var UserLevelRelationship
     */
    public $rel;

    /**
     * A href link to the resources or resource actions as indicated in the corresponding rel value.
     *
     * @var string
     */
    public $href;
}