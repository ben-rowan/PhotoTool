<?php declare(strict_types=1);

namespace BenRowan\PhotoTool\Command;

use BenRowan\PhotoTool\Service\DuplicateFinder;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FindDuplicatesCommand extends Command
{
    public const ARG_DIRECTORY = 'directory';

    protected static $defaultName = 'find:duplicates';

    protected static $defaultDescription = 'Find duplicate files.';

    public function __construct(
        private DuplicateFinder $duplicateFinder
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('Finds duplicate files in the provided directory and subdirectories')
            ->addArgument(
                self::ARG_DIRECTORY,
                InputArgument::REQUIRED,
                'The directory to start searching from'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stderr = $output instanceof ConsoleOutputInterface
                ? $output->getErrorOutput()
                : $output;

        try {
            $directory = $input->getArgument(self::ARG_DIRECTORY);

            $stderr->write(sprintf("Finding duplicate files in '%s'\n\n", $directory));

            /**
             * Setup the hooks for updating the progress bars
             */

            $fileSizeProgress = new ProgressBar($output, 1);
            $fileSizeProgress->setFormat('verbose');

            $this->duplicateFinder->addHook(
                DuplicateFinder::HOOK_FILE_SIZE_START,
                function (array $context) use ($stderr, $fileSizeProgress) {
                    $fileSizeProgress->setMaxSteps(
                        $context[DuplicateFinder::CONTEXT_FILE_COUNT]
                    );

                    $stderr->writeln('Sorting files by file size:');
                    $fileSizeProgress->start();
                }
            );

            $this->duplicateFinder->addHook(
                DuplicateFinder::HOOK_FILE_SIZE_STEP,
                function () use ($fileSizeProgress) {
                    $fileSizeProgress->advance();
                }
            );

            $this->duplicateFinder->addHook(
                DuplicateFinder::HOOK_FILE_SIZE_END,
                function () use ($stderr, $fileSizeProgress) {
                    $fileSizeProgress->finish();
                    $stderr->write("\n\n");
                }
            );

            $firstNBytesProgress = new ProgressBar($output, 1);
            $firstNBytesProgress->setFormat('verbose');

            $this->duplicateFinder->addHook(
                DuplicateFinder::HOOK_FIRST_N_BYTES_START,
                function (array $context) use ($stderr, $firstNBytesProgress) {
                    $firstNBytesProgress->setMaxSteps(
                        $context[DuplicateFinder::CONTEXT_FILE_COUNT]
                    );

                    $stderr->writeln('Sorting files by first N bytes:');
                    $firstNBytesProgress->start();
                }
            );

            $this->duplicateFinder->addHook(
                DuplicateFinder::HOOK_FIRST_N_BYTES_STEP,
                function () use ($firstNBytesProgress) {
                    $firstNBytesProgress->advance();
                }
            );

            $this->duplicateFinder->addHook(
                DuplicateFinder::HOOK_FIRST_N_BYTES_END,
                function () use ($stderr, $firstNBytesProgress) {
                    $firstNBytesProgress->finish();
                    $stderr->write("\n\n");
                }
            );

            $fullFileHashProgress = new ProgressBar($output, 1);
            $fullFileHashProgress->setFormat('verbose');

            $this->duplicateFinder->addHook(
                DuplicateFinder::HOOK_FULL_FILE_HASH_START,
                function (array $context) use ($stderr, $fullFileHashProgress) {
                    $fullFileHashProgress->setMaxSteps(
                        $context[DuplicateFinder::CONTEXT_FILE_COUNT]
                    );

                    $stderr->writeln('Sorting files by file hash:');
                    $fullFileHashProgress->start();
                }
            );

            $this->duplicateFinder->addHook(
                DuplicateFinder::HOOK_FULL_FILE_HASH_STEP,
                function () use ($fullFileHashProgress) {
                    $fullFileHashProgress->advance();
                }
            );

            $this->duplicateFinder->addHook(
                DuplicateFinder::HOOK_FULL_FILE_HASH_END,
                function () use ($stderr, $fullFileHashProgress) {
                    $fullFileHashProgress->finish();
                    $stderr->write("\n\n");
                }
            );

            /**
             * Find those duplicates!
             */
            
            $duplicates = $this->duplicateFinder->findDuplicates($directory);

            $peakMemoryUsageMb = memory_get_peak_usage() / 1000000;
            $stderr->writeln(
                sprintf('Peak memory usage: %s Mb', number_format($peakMemoryUsageMb, 0))
            );

            // TODO: improve the output options.
            foreach ($duplicates as $duplicate) {
                $output->writeln(implode(',', $duplicate));
            }

            return Command::SUCCESS;
        }
        catch (Exception $e) {
            $stderr->writeln($e->getMessage());

            return Command::FAILURE;
        }
    }
}