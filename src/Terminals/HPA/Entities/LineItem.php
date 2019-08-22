<?php

namespace GlobalPayments\Api\Terminals\HPA\Entities;

class LineItem
{

    /*
     * Left justified text to display for each line item (Mandatory)
     */
    public $leftText;
    
    /*
     * Right justified text to display for each line item. Will overwrite left justified text if
     * overlap, with 1 space between left and right text (Optional)
     */
    public $rightText = null;
    
    /*
     * Left justified running text to display for all line items (Optional)
     */
    public $runningLeftText = null;
    
    /*
     * Right justified running text to display for all line items. Will overwrite left justified text
     * if overlap, with 1 space between left and right text (Optional)
     */
    public $runningRightText = null;
}
