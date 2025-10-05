<?php

declare(strict_types=1);

namespace App\Tests\ClassGenerator\Domain;

use App\ClassGenerator\Domain\Model\ClassCollection;
use App\ClassGenerator\Domain\Model\ClassItem;
use App\ClassGenerator\Domain\Model\Validation\ClassExists;
use App\ClassGenerator\Domain\Model\Validation\ClassExistsValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @extends ConstraintValidatorTestCase<ClassExistsValidator>
 */ class ClassExistsValidatorTest extends ConstraintValidatorTestCase
{
    /**
     * @return array<string, list<mixed>>
     */
    public static function provideInvalidValues(): array
    {
        return [
            'null' => [null],
            'string' => ['toto'],
            'object' => [new ClassItem()],
        ];
    }

    /**
     * @return array<string, list<mixed>>
     */
    public static function provideInvalidConstraints(): array
    {
        return [
            'not blank constraint' => [new NotBlank()],
            'valid constraint' => [new Valid()],
            'not null constraint' => [new NotNull()],
        ];
    }

    #[DataProvider('provideInvalidValues')]
    public function testValueTypeMustBeClassCollection(mixed $value): void
    {
        // Given : I provide a value with a bad type and the right constraint
        // When : I validate
        // Then : I expect an Exception
        $this->expectException(UnexpectedValueException::class);
        $this->validator->validate($value, new ClassExists());
    }

    #[DataProvider('provideInvalidConstraints')]
    public function testConstraintTypeMustBeClassExists(Constraint $constraint): void
    {
        // Given : I provide a value with a good type and a constraint of another type
        // When : I validate
        // Then : I expect an Exception
        $this->expectException(UnexpectedValueException::class);
        $this->validator->validate(new ClassCollection(), $constraint);
    }

    public function testOkNoFqn(): void
    {
        // Given : I provide a value with a good type
        [$classA, $classB, $classC] = self::getClassItems();
        $classB->setParent($classA);
        $classColl = new ClassCollection();
        $classColl->setClasses([
            $classA, $classB, $classC,
        ]);
        // Given : I provide the right constraint
        $constraint = new ClassExists();

        // When : I validate
        $this->validator->validate($classColl, $constraint);

        // Then : I expect No violation
        $this->assertNoViolation();
    }

    /**
     * @return ClassItem[]
     */
    private static function getClassItems(): array
    {
        $classA = new ClassItem();
        $classA->setName('A');
        $classB = new ClassItem();
        $classB->setName('B');
        $classC = new ClassItem();
        $classC->setName('App\C');

        return [$classA, $classB, $classC];
    }

    public function testOkFqn(): void
    {
        // Given : I provide a value with a good type
        [$classA, $classB, $classC] = self::getClassItems();
        $classB->setParent($classC);
        $classColl = new ClassCollection();
        // Given : one of the classes references as parent a class that does not exist in ClassCollection
        // but have a Fqn
        $classColl->setClasses([
            $classA, $classB,
        ]);
        // Given : I provide the right constraint
        $constraint = new ClassExists();

        // When : I validate
        $this->validator->validate($classColl, $constraint);

        // Then : I expect No violation
        $this->assertNoViolation();
    }

    public function testKoNoFqn(): void
    {
        // Given : I provide a value with a good type
        [$classA, $classB, $classC] = self::getClassItems();
        $classB->setParent($classA);
        $classColl = new ClassCollection();
        // Given : one of the classes references as parent a class that does not exist in ClassCollection
        $classColl->setClasses([
            $classB, $classC,
        ]);
        // Given : I provide the right constraint
        $constraint = new ClassExists();
        $constraint->message = 'dummy';

        // When : I validate
        $this->validator->validate($classColl, $constraint);

        // Then : I expect a violation
        $this->buildViolation($constraint->message)
            ->setParameter('{{ class name }}', $classB->getParent()->getName())
            ->atPath('property.path.parent.name')
            ->assertRaised();
    }

    protected function createValidator(): ConstraintValidatorInterface
    {
        return new ClassExistsValidator();
    }
}
