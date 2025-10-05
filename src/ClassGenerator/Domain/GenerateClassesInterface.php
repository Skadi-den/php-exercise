<?php

namespace App\ClassGenerator\Domain;

use App\ClassGenerator\Domain\Exceptions\ClassesGenerationSerializationException;
use App\ClassGenerator\Domain\Exceptions\ClassesGenerationValidationException;
use App\ClassGenerator\Domain\Exceptions\FileWriterException;
use App\ClassGenerator\Domain\Model\ClassCollection;

interface GenerateClassesInterface
{
    /**
     * @throws ClassesGenerationSerializationException
     * @throws ClassesGenerationValidationException
     */
    public function generate(string $fileContent, string $format, bool $allowFqn): ClassCollection;

    /**
     * @throws FileWriterException
     */
    public function writeToPhpFile(ClassCollection $classCollection): void;
}
