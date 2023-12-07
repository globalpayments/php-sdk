<?php

namespace GlobalPayments\Api\Entities;

class FileList
{
    private array $files;

    public function __construct(FileUploaded ...$file)
    {
        $this->files = $file;
    }

    public function add(FileUploaded $fileUploaded) : void
    {
        $this->files[] = $fileUploaded;
    }

    public function all() : array
    {
        return $this->files;
    }
}