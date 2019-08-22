<?php

namespace GlobalPayments\Api\Terminals\HPA\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class HpaSendFileType extends Enum
{
    /*
     * Displayed when the SIP device is in an idle state. The SIP device boots up into an idle state. 
     * Every command from the POS puts SIP device in an active state, and it returns to an idle state 
     * by a Reset command
     * 
     * Pixel Dimensions (H*W):
     *  272x480 (iSC250)
     *  240x320 (iPP350)
     */
    const IDLELOGO = 'IDLELOGO.JPG';
    
    /*
     * Displayed at the top of the SIP device screen at all times during POS driven activities. 
     * It is not shown when doing HeartSIP driven activities such as auto EOD processing
     * 
     * Pixel Dimensions (H*W):
     *  40x320 (iPP350)
     *  60x480 (iSC250)
     */
    const BANNER = 'BANNER.JPG';
}
