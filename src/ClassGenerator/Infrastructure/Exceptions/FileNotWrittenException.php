<?php

namespace App\ClassGenerator\Infrastructure\Exceptions;

class FileNotWrittenException extends \Exception
{
    public function __construct(string $filePath = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(sprintf('The file "%s" can not be written.', $filePath), $code, $previous);
    }
}
