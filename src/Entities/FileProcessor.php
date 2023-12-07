<?php

namespace GlobalPayments\Api\Entities;

class FileProcessor
{
    public string $resourceId;
    public string $uploadUrl;
    public string $expirationDate;
    public string $status;
    public string $createdDate;
    public ?string $totalRecordCount;
    public string $responseCode;
    public string $responseMessage;
    public FileList $files;
}