<?php

namespace App\ClassGenerator\Domain\Model;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Serializer\Annotation\SerializedPath;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class ClassItem
{
    #[Assert\NotBlank]
    // class name rules : https://www.php.net/manual/en/language.oop5.basic.php
    #[Assert\Regex(
        pattern: '/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/',
        message: "Class name {{ value }} invalid. Pattern applied : '{{ pattern }}'. The class name must match these rules : https://www.php.net/manual/en/language.oop5.basic.php .",
        groups: ['deny-fqn'],
    )]
    #[Assert\Regex(
        pattern: '/^([a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*)(\\\[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*)*$/',
        message: "Class name {{ value }} invalid. Pattern applied : '{{ pattern }}'. The class name must match these rules : https://www.php.net/manual/en/language.oop5.basic.php .",
        groups: ['allow-fqn'],
    )]
    #[Assert\Regex(
        pattern: '/^_$/',
        message: 'The class name cannot be an underscore. It must match these rules : https://www.php.net/manual/en/language.oop5.basic.php .',
        match: false
    )]
    // todo: add list of PHP reserved words
    private string $name;
    #[Assert\Valid]
    private ?self $parent = null;

    public function __toString(): string
    {
        if ($this->hasParent()) {
            return $this->getName().' extends '.$this->getParent()->getName();
        }

        return $this->getName();
    }

    public function hasParent(): bool
    {
        return null !== $this->parent;
    }

    public function getName(): string
    {
        return $this->name;
    }

    #[SerializedName('@id')]
    #[Groups('xml:read')]
    public function setName(string $name): void
    {
        $words = explode(' ', $name);
        array_walk($words, function (&$value) {
            $value = ucfirst($value);
        });
        $this->name = implode($words);
    }

    public function getParent(): ?ClassItem
    {
        return $this->parent;
    }

    #[SerializedPath('[parent][class]')]
    #[Groups('xml:read')]
    public function setParent(?ClassItem $parent): void
    {
        $this->parent = $parent;
    }
}
