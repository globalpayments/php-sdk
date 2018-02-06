<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

/// <summary>
/// Indicates a reason for the transaction.
/// </summary>
/// <remarks>
/// This is typically used for returns/reversals.
/// </remarks>
class ReasonCode extends Enum
{
    /// <summary>
    /// Indicates fraud.
    /// </summary>
    const FRAUD = 'FRAUD';

    /// <summary>
    /// Indicates a false positive.
    /// </summary>
    const FALSE_POSITIVE = 'FALSEPOSITIVE';

    /// <summary>
    /// Indicates desired good is out of stock.
    /// </summary>
    const OUT_OF_STOCK = 'OUTOFSTOCK';

    /// <summary>
    /// Indicates desired good is in of stock.
    /// </summary>
    const IN_STOCK = 'INSTOCK';

    /// <summary>
    /// Indicates another reason.
    /// </summary>
    const OTHER = 'OTHER';

    /// <summary>
    /// Indicates reason was not given.
    /// </summary>
    const NOT_GIVEN = 'NOTGIVEN';
}
