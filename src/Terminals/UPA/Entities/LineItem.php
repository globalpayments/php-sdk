<?php

namespace GlobalPayments\Api\Terminals\UPA\Entities;

class LineItem
{

    /*
     * Left justified text to display for each line item (Mandatory)
     * Contains the left-justified display characters, for example, the purchased
     * items.
     */
    public $lineItemLeft;

    /*
     * Contains the right-justified display characters, for example, the item’s price.(Optional)s
     */
    public $lineItemRight = null;
}
