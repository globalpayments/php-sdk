<?php

namespace GlobalPayments\Api\Terminals\HPA\Entities;

class SendFileData
{
    
    /*
     * Value from Enum HpaSendFileType
     */
    public $imageType;
    
    /*
     * Left justified text to display for each line item (Mandatory)
     */
    public $imageLocation;
}
