<?php

namespace App\ClassGenerator\Infrastructure;

use App\ClassGenerator\Domain\FileReaderInterface;
use App\ClassGenerator\Infrastructure\Exceptions\FileMimeTypeNotValidException;
use App\ClassGenerator\Infrastructure\Exceptions\FileNotFoundException;
use App\ClassGenerator\Infrastructure\Exceptions\FileNotReadableException;

class FileReader implements FileReaderInterface
{
    /**
     * @throws FileNotReadableException
     * @throws FileMimeTypeNotValidException
     * @throws FileNotFoundException
     */
    public function readXmlFile(string $filePath): string
    {
        $fileValidator = new XmlFileValidator();
        $fileValidator->validate($filePath);

        $content = file_get_contents($filePath);
        if (false === $content) {
            throw new FileNotReadableException();
        }

        return $content;
    }
}
