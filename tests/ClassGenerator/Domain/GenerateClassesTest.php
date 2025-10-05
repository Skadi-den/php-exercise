<?php

namespace App\Tests\ClassGenerator\Domain;

use App\ClassGenerator\Domain\Exceptions\ClassesGenerationSerializationException;
use App\ClassGenerator\Domain\Exceptions\ClassesGenerationValidationException;
use App\ClassGenerator\Domain\GenerateClasses;
use App\ClassGenerator\Domain\Model\ClassCollection;
use App\ClassGenerator\Domain\Model\ClassCollectionInterface;
use App\ClassGenerator\Infrastructure\PhpFileWriter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GenerateClassesTest extends KernelTestCase
{
    private static string $resourcesDir;
    private MockObject $validatorMock;
    private MockObject $writerMock;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        /** @var string $projectDir */
        $projectDir = static::getContainer()->getParameter('kernel.project_dir');
        self::$resourcesDir = $projectDir.'/tests/resources/ClassGenerator/';
    }

    /**
     * @return array<string, list<bool|string>>
     */
    public static function provideAllowFqn(): array
    {
        return [
            'allow fqn' => [true, 'allow-fqn'],
            'deny fqn' => [false, 'deny-fqn'],
        ];
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->writerMock = $this->createMock(PhpFileWriter::class);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);
        static::getContainer()->set(PhpFileWriter::class, $this->writerMock);
        static::getContainer()->set(ValidatorInterface::class, $this->validatorMock);
    }

    public function testGenerateWithWrongFileFormat(): void
    {
        // Given : I provide a xml string and format = json
        /** @var string $content */
        $content = file_get_contents(self::$resourcesDir.'sample-basic.xml');
        $format = 'json';

        // Then : I expect that no validation nor write occurs
        $this->validatorMock->expects($this->never())->method('validate');
        $this->writerMock->expects($this->never())->method('write');

        // Then : I expect an Exception
        $this->expectException(ClassesGenerationSerializationException::class);

        // When : I run generate
        /** @var GenerateClasses $service */
        $service = static::getContainer()->get(GenerateClasses::class);
        $service->generate($content, $format, false);
    }

    #[DataProvider('provideAllowFqn')]
    public function testGenerate(bool $allowFqn, string $validationGroup): void
    {
        // Given : I provide a xml string
        /** @var string $content */
        $content = file_get_contents(self::$resourcesDir.'sample-basic.xml');

        // Then : I expect that validation occurs
        $this->validatorMock
            ->expects($this->once())->method('validate')
            ->with(
                $this->isInstanceOf(ClassCollectionInterface::class),
                $this->anything(),
                ['Default', $validationGroup]
            )
            ->willReturn($this->createMock(ConstraintViolationListInterface::class));

        // Then : I expect that no write occurs
        $this->writerMock->expects($this->never())->method('write');

        // When : I run generate
        /** @var GenerateClasses $service */
        $service = static::getContainer()->get(GenerateClasses::class);
        $result = $service->generate($content, 'xml', $allowFqn);

        // Then : I expect a result of type ClassCollection
        $this->assertInstanceOf(ClassCollection::class, $result);
    }

    public function testGenerateValidationError(): void
    {
        // Given : I provide a xml string
        /** @var string $content */
        $content = file_get_contents(self::$resourcesDir.'sample-basic.xml');

        // Given : validation errors occurs
        $errorsMock = ConstraintViolationList::createFromMessage('toto');

        $this->validatorMock
            ->expects($this->once())->method('validate')
            ->with(
                $this->isInstanceOf(ClassCollectionInterface::class),
                $this->anything(),
                $this->isArray()
            )
            ->willReturn($errorsMock);

        // Then : I expect that no write occurs
        $this->writerMock->expects($this->never())->method('write');

        // Then : I expect an Exception
        $this->expectException(ClassesGenerationValidationException::class);
        $this->expectExceptionMessage('toto');

        // When : I run generate
        /** @var GenerateClasses $service */
        $service = static::getContainer()->get(GenerateClasses::class);
        $service->generate($content, 'xml', false);
    }

    public function testWriteToPhpFile(): void
    {
        // Then : I expect that write occurs
        $this->writerMock->expects($this->once())->method('write');

        // When : I run writeToPhpFile
        /** @var GenerateClasses $service */
        $service = static::getContainer()->get(GenerateClasses::class);
        $service->writeToPhpFile(new ClassCollection());
    }
}
