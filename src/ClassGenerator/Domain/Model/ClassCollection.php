<?php

namespace App\ClassGenerator\Domain\Model;

use App\ClassGenerator\Domain\Model\Validation\ClassExists;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ClassExists]
class ClassCollection implements ClassCollectionInterface
{
    /**
     * @var ClassItem[]
     */
    #[Assert\Valid]
    private array $classes = [];

    /**
     * @return ClassItem[] $class
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    /**
     * @param ClassItem[] $classes
     */
    #[SerializedName('class')]
    #[Groups('xml:read')]
    public function setClasses(array $classes): void
    {
        $this->classes = $classes;
    }
}
