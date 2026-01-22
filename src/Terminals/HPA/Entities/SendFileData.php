<?php

namespace GlobalPayments\Api\Terminals\HPA\Entities;

class SendFileData
{
    
    /*
     * Value from Enum SendFileType
     */
    public ?string $imageType = null;
    
    /*
     * Left justified text to display for each line item (Mandatory)
     */
    public ?string $imageLocation = null;
}
