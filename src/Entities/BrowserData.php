<?php

namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Entities\Enums\ChallengeWindowSize;
use GlobalPayments\Api\Entities\Enums\ColorDepth;

class BrowserData
{
    /** @var string */
    public $acceptHeader;
    /** @var string */
    public $colorDepth;
    /** @var string */
    public $ipAddress;
    /** @var string */
    public $javaEnabled;
    /** @var string */
    public $javaScriptEnabled;
    /** @var string */
    public $language;
    /** @var string */
    public $screenHeight;
    /** @var string */
    public $screenWidth;
    /** @var string */
    public $challengWindowSize;
    /** @var string */
    public $timeZone;
    /** @var string */
    public $userAgent;
}
