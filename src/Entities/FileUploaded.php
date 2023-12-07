<?php

namespace GlobalPayments\Api\Entities;

class FileUploaded
{
    public string $fileId;
    public string $fileName;
    public string $timeCreated;
    public string $url;
    public string $expirationDate;
}