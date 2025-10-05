<?php

namespace App\ClassGenerator\Domain\Model;

interface ClassCollectionInterface
{
    /**
     * @return ClassItem[] $class
     */
    public function getClasses(): array;
}
