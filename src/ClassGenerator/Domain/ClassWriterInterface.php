<?php

namespace App\ClassGenerator\Domain;

use App\ClassGenerator\Domain\Model\ClassCollectionInterface;

interface ClassWriterInterface
{
    /**
     * @throws \Throwable
     */
    public function write(ClassCollectionInterface $classes): void;
}
