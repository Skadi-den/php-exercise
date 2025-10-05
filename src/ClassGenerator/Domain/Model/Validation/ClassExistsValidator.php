<?php

namespace App\ClassGenerator\Domain\Model\Validation;

use App\ClassGenerator\Domain\Model\ClassCollection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ClassExistsValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ClassExists) {
            throw new UnexpectedValueException($constraint, ClassExists::class);
        }
        if (!$value instanceof ClassCollection) {
            throw new UnexpectedValueException($constraint, ClassCollection::class);
        }

        $baseClasses = [];
        $referredClasses = [];
        foreach ($value->getClasses() as $class) {
            $baseClasses[] = $class->getName();
            if ($class->getParent()) {
                $referredClasses[] = $class->getParent()->getName();
            }
        }

        foreach ($referredClasses as $referredClassName) {
            $items = explode('\\', $referredClassName);
            if (count($items) > 1) {
                // fqn, so we assume it exists somewhere else
                continue;
            }
            if (!in_array($referredClassName, $baseClasses, true)) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ class name }}', $referredClassName)
                    ->atPath('parent.name')
                    ->addViolation();
            }
        }
    }
}
