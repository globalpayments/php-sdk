<?php

namespace GlobalPayments\Api\Terminals\Entities;

use GlobalPayments\Api\Terminals\Enums\DisplayOption;
use GlobalPayments\Api\Terminals\Enums\UDFileTypes;

/**
 * Entity for the user defined screen  of the device
 * Class UDScreen
 * @package GlobalPayments\Api\Terminals\Entities
 */
class UDData
{
    /** @var UDFileTypes Contains the parameters for the file to be loaded */
    public string $fileType;

    /** @var int Slot number of the data file */
    public int $slotNum;

    /** @var string Filename of the file to be stored in the device. Must include the file extension.
     * Must not contain a file path.
     */
    public string $fileName;

    /** @var string|DisplayOption Display change after exiting the screen currently displayed */
    public string|DisplayOption $displayOption;

    /** @var string User Defined filename and path, but the path is a sub folder to the destination */
    public string $file;

    /** @var string Full path of the file you want to inject/use */
    public string $localFile;
}