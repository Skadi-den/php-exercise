<?php

namespace App\ClassGenerator\Infrastructure\Exceptions;

class FileNotFoundException extends \Exception
{
    public function __construct(string $filePath = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(sprintf('The file "%s" does not exist or is not readable.', $filePath), $code, $previous);
    }
}
