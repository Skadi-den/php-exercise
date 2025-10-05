<?php

namespace App\ClassGenerator\Domain\Exceptions;

class ClassesGenerationValidationException extends \Exception
{
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct('Classes generation validation exception : '.$message, $code, $previous);
    }
}
