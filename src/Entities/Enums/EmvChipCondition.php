<?php

namespace GlobalPayments\Api\Entities\Enums;

/**
 * EMV Chip Condition enumeration
 * 
 * Indicates the status of previous chip read attempts for fallback scenarios
 */
class EmvChipCondition
{
    /**
     * Previous chip read was successful
     */
    const CHIP_FAILED_PREV_SUCCESS = 'CHIP_FAILED_PREV_SUCCESS';
    
    /**
     * Previous chip read also failed
     */
    const CHIP_FAILED_PREV_FAILED = 'CHIP_FAILED_PREV_FAILED';
}
