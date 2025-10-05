<?php

namespace App\ClassGenerator\Domain;

interface FileReaderInterface
{
    /**
     * @throws \Throwable
     */
    public function readXmlFile(string $filePath): string;
}
