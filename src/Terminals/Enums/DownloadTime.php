<?php

namespace GlobalPayments\Api\Terminals\Enums;

use GlobalPayments\Api\Entities\Enum;

/**
 * Indicates when to perform the download. Supported values are:
 *
 * NOW : Initiate download immediately.
 *
 * EOD : Initiate download after next EOD processing.
 *
 * YYYYMMDDHHMMSS : Initiate download at specified date and time
 *
 */
class DownloadTime extends Enum
{
    const NOW = "NOW";
    const EOD = "EOD";
}
