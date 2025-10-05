<?php

namespace App\Tests\ClassGenerator\Application;

use App\ClassGenerator\Infrastructure\PhpFileWriter;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class GeneratePhpClassesFromXmlCommandTest extends KernelTestCase
{
    private const string APP_NAME = 'app:php-class-generator:from-xml';
    private static string $resourcesDir;
    private CommandTester $commandTester;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        /** @var string $projectDir */
        $projectDir = static::getContainer()->getParameter('kernel.project_dir');
        self::$resourcesDir = $projectDir.'/tests/resources/ClassGenerator/';
    }

    public function setUp(): void
    {
        parent::setUp();
        $writerMock = $this->createMock(PhpFileWriter::class);
        static::getContainer()->set(PhpFileWriter::class, $writerMock);

        $application = new Application(static::$kernel);
        $command = $application->find(self::APP_NAME);
        $this->commandTester = new CommandTester($command);
    }

    public function testFileNotExists(): void
    {
        // Given : I use a path to a non-existing file
        $filename = 'undefined.xml';
        $path = self::$resourcesDir.$filename;

        // When : I run GeneratePhpClassesFromXml command
        $this->commandTester->execute([
            'command' => self::APP_NAME,
            'filePath' => $path,
        ]);

        // Then : Command is in failure with error message
        $this->assertEquals(Command::FAILURE, $this->commandTester->getStatusCode());
        $this->assertStringContainsString(
            sprintf('The file "%s" does not exist or is not readable.', $path),
            $this->cleanUpCommandDisplay()
        );
    }

    public function testTextFile(): void
    {
        // Given : I use a path to a non-existing file
        $filename = 'sample.txt';
        $path = self::$resourcesDir.$filename;

        // When : I run GeneratePhpClassesFromXml command
        $this->commandTester->execute([
            'command' => self::APP_NAME,
            'filePath' => $path,
        ]);

        // Then : Command is in failure with error message
        $this->assertEquals(Command::FAILURE, $this->commandTester->getStatusCode());
        $this->assertStringContainsString(
            sprintf(
                'The file "%s" has mime type "%s". The mime types allowed are: "%s".',
                $path,
                'text/plain',
                'application/xml, text/xml'
            ),
            $this->cleanUpCommandDisplay()
        );
    }

    public function testEmptyFile(): void
    {
        // Given : I use a path to a non-existing file
        $filename = 'sample-empty.xml';
        $path = self::$resourcesDir.$filename;

        // When : I run GeneratePhpClassesFromXml command
        $this->commandTester->execute([
            'command' => self::APP_NAME,
            'filePath' => $path,
        ]);

        // Then : Command is in failure with error message
        $this->assertEquals(Command::INVALID, $this->commandTester->getStatusCode());
        $this->assertStringContainsString(
            'No class found in provided file',
            $this->cleanUpCommandDisplay()
        );
    }

    public function testParentClassNotExist(): void
    {
        // Given : I use a path to a valid file but with a parent class that is not declared
        $filename = 'sample-class-not-exists.xml';
        $path = self::$resourcesDir.$filename;

        // When : I run GeneratePhpClassesFromXml command
        $this->commandTester->execute([
            'command' => self::APP_NAME,
            'filePath' => $path,
        ]);

        // Then : Command is in failure with error message
        $this->assertEquals(Command::FAILURE, $this->commandTester->getStatusCode());
        $this->assertStringContainsString(
            sprintf(
                'The class "%s" is referenced while it has not been declared yet.',
                'Gad'
            ),
            $this->cleanUpCommandDisplay()
        );
    }

    public function testParentClassDeclaredLater(): void
    {
        // Given : I use a path to a valid file but with a parent class that is not declared yet
        $filename = 'sample-class-is-declared-later.xml';
        $path = self::$resourcesDir.$filename;

        // When : I run GeneratePhpClassesFromXml command
        $this->commandTester->execute([
            'command' => self::APP_NAME,
            'filePath' => $path,
        ]);

        // Then : Command is successful
        $this->assertEquals(Command::SUCCESS, $this->commandTester->getStatusCode());
        $this->assertStringContainsString(
            sprintf(
                'There are %s class(es) in this file',
                4
            ),
            $this->cleanUpCommandDisplay()
        );
    }

    public function testBasicSuccess(): void
    {
        // Given : I use a path to a valid file
        $filename = 'sample-basic.xml';
        $path = self::$resourcesDir.$filename;

        // When : I run GeneratePhpClassesFromXml command
        $this->commandTester->execute([
            'command' => self::APP_NAME,
            'filePath' => $path,
        ]);

        // Then : Command is successful
        $this->assertEquals(Command::SUCCESS, $this->commandTester->getStatusCode());
        $this->assertStringContainsString(
            sprintf(
                'There are %s class(es) in this file',
                4
            ),
            $this->cleanUpCommandDisplay()
        );
    }

    public function testWithFqnFailure(): void
    {
        // Given : I use a path to a valid file with a fully qualified name in a parent class name
        $filename = 'sample-with-fqn.xml';
        $path = self::$resourcesDir.$filename;

        // When : I run GeneratePhpClassesFromXml command
        $this->commandTester->execute([
            'command' => self::APP_NAME,
            'filePath' => $path,
        ]);

        // Then : Command is successful
        $this->assertEquals(Command::FAILURE, $this->commandTester->getStatusCode());
        $this->assertStringContainsString(
            sprintf(
                'Class name "%s" invalid. Pattern applied',
                "App\Gad"
            ),
            $this->cleanUpCommandDisplay()
        );
    }

    public function testWithFqnSuccess(): void
    {
        // Given : I use a path to a valid file with a fully qualified name in a parent class name
        $filename = 'sample-with-fqn.xml';
        $path = self::$resourcesDir.$filename;

        // When : I run GeneratePhpClassesFromXml command with allow_fqn option
        $this->commandTester->execute(
            input: [
                'command' => self::APP_NAME,
                'filePath' => $path,
                '--allow_fqn' => true,
            ],
        );

        // Then : Command is successful
        $this->assertEquals(Command::SUCCESS, $this->commandTester->getStatusCode());
        $this->assertStringContainsString(
            sprintf(
                'There are %s class(es) in this file',
                5
            ),
            $this->cleanUpCommandDisplay()
        );
    }

    private function cleanUpCommandDisplay(): string
    {
        return preg_replace('/\s{2,}/m', ' ', $this->commandTester->getDisplay());
    }
}
