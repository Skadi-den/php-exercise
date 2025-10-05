<?php

namespace App\ClassGenerator\Infrastructure\Exceptions;

class FileMimeTypeNotValidException extends \Exception
{
    /**
     * @param string[] $allowedMimeTypes
     */
    public function __construct(string $filePath = '', string $fileMimeType = '', array $allowedMimeTypes = [], int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(
            sprintf(
                'The file "%s" has mime type "%s". The mime types allowed are: "%s".',
                $filePath,
                $fileMimeType,
                implode(', ', $allowedMimeTypes)
            ),
            $code,
            $previous
        );
    }
}
