<?php

namespace GlobalPayments\Api\Terminals\Enums;

use GlobalPayments\Api\Entities\Enum;

class UDFileTypes extends Enum
{
    /* html and htm files are supported */
    const HTML5 = 'HTML5';
    /* Only jpg, jpeg, bmp, png, and gif files are supported */
    const IMG = 'IMG';
    /* Only mov, mp4 and 3pg files are supported  */
    const MOV = 'MOV';
}