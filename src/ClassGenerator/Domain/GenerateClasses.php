<?php

namespace App\ClassGenerator\Domain;

use App\ClassGenerator\Domain\Exceptions\ClassesGenerationSerializationException;
use App\ClassGenerator\Domain\Exceptions\ClassesGenerationValidationException;
use App\ClassGenerator\Domain\Exceptions\FileWriterException;
use App\ClassGenerator\Domain\Model\ClassCollection;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class GenerateClasses implements GenerateClassesInterface
{
    public function __construct(
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private ClassWriterInterface $phpFileWriter,
    ) {
    }

    /**
     * @throws ClassesGenerationValidationException
     * @throws ClassesGenerationSerializationException
     */
    public function generate(string $fileContent, string $format, bool $allowFqn): ClassCollection
    {
        try {
            $classes = $this->serializer->deserialize(
                data: $fileContent,
                type: ClassCollection::class,
                format: $format,
                context: [
                    AbstractNormalizer::GROUPS => [$format.':read'],
                ]
            );
        } catch (ExceptionInterface $e) {
            throw new ClassesGenerationSerializationException(message: $e->getMessage(), previous: $e);
        }
        $errors = $this->validator->validate(
            value: $classes,
            groups: $this->getValidationGroupsFromOption($allowFqn),
        );
        if (count($errors) > 0) {
            throw new ClassesGenerationValidationException((string) $errors);
        }

        return $classes;
    }

    /**
     * @return string[]
     */
    private function getValidationGroupsFromOption(bool $allowFqn): array
    {
        $validationGroups = ['Default'];
        $validationGroups[] = $allowFqn ? 'allow-fqn' : 'deny-fqn';

        return $validationGroups;
    }

    public function writeToPhpFile(ClassCollection $classCollection): void
    {
        try {
            $this->phpFileWriter->write($classCollection);
        } catch (FileWriterException $e) {
            throw new FileWriterException(message: $e->getMessage(), previous: $e);
        }
    }
}
