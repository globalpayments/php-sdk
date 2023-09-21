<?php

namespace GlobalPayments\Api\Entities\Enums;

use GlobalPayments\Api\Entities\Enum;

class FileType extends Enum
{
    const TIF = 'TIF';
    const TIFF = 'TIFF';
    const PDF = 'PDF';
    const BMP = 'BMP';
    const JPEG = 'JPEG';
    const GIF = 'GIF';
    const PNG = 'PNG';
    const DOC = 'DOC';
    const DOCX = 'DOCX';
}