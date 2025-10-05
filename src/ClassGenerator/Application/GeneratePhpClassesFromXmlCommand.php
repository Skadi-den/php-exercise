<?php

namespace App\ClassGenerator\Application;

use App\ClassGenerator\Domain\FileReaderInterface;
use App\ClassGenerator\Domain\GenerateClassesInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:php-class-generator:from-xml', description: 'Add a short description for your command', )]
class GeneratePhpClassesFromXmlCommand extends Command
{
    public function __construct(
        private readonly GenerateClassesInterface $generateClasses,
        private readonly FileReaderInterface $fileReader,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('filePath', InputArgument::REQUIRED, 'file path');
        $this->addOption('allow_fqn', 'a', InputOption::VALUE_NONE, 'allow classes with fully qualified name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = $input->getArgument('filePath');

        try {
            $fileContent = $this->fileReader->readXmlFile($filePath);
        } catch (\Throwable $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        try {
            $classes = $this->generateClasses->generate($fileContent, 'xml', $input->getOption('allow_fqn'));
        } catch (\Throwable $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }
        $nbClasses = count($classes->getClasses());
        if (0 === $nbClasses) {
            $io->warning('No class found in provided file');

            return Command::INVALID;
        }
        $io->info(sprintf('There are %s class(es) in this file', $nbClasses));

        try {
            $this->generateClasses->writeToPhpFile($classes);
        } catch (\Throwable $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
