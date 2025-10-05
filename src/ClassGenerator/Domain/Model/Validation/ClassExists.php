<?php

namespace App\ClassGenerator\Domain\Model\Validation;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ClassExists extends Constraint
{
    public string $message = 'The class "{{ class name }}" is referenced while it has not been declared yet.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
