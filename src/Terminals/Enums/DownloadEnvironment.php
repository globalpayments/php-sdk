<?php

namespace GlobalPayments\Api\Terminals\Enums;

use GlobalPayments\Api\Entities\Enum;

class DownloadEnvironment extends Enum
{
    const DEVELOPMENT = "SSLHPS.TEST.HPSDNLD.NET";
    const PRODUCTION = "MSSLHPS.PROD.HPSDNLD.NET";
}
