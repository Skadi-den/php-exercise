<?php

namespace App\ClassGenerator\Infrastructure\Exceptions;

class OutputDirNotValidException extends \Exception
{
    public function __construct(string $dir = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(sprintf('The dir "%s" can not be created.', $dir), $code, $previous);
    }
}
