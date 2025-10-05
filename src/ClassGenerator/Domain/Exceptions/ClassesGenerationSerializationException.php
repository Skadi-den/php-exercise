<?php

namespace App\ClassGenerator\Domain\Exceptions;

class ClassesGenerationSerializationException extends \Exception
{
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct('Classes generation serialization exception : '.$message, $code, $previous);
    }
}
