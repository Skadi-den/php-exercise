<?php

namespace App\ClassGenerator\Infrastructure;

use App\ClassGenerator\Infrastructure\Exceptions\FileMimeTypeNotValidException;
use App\ClassGenerator\Infrastructure\Exceptions\FileNotFoundException;

class XmlFileValidator
{
    public const array MIME_TYPES_ALLOWED = [
        'application/xml',
        'text/xml',
    ];

    /**
     * @throws FileNotFoundException
     * @throws FileMimeTypeNotValidException
     */
    public function validate(string $filePath): void
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new FileNotFoundException($filePath);
        }
        $fileMimeType = mime_content_type($filePath);
        if (!in_array($fileMimeType, self::MIME_TYPES_ALLOWED, true)) {
            throw new FileMimeTypeNotValidException($filePath, (string) $fileMimeType, self::MIME_TYPES_ALLOWED);
        }
    }
}
