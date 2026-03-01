<?php declare(strict_types=1);

namespace BenRowan\PhotoTool\Command;

use BenRowan\PhotoTool\Service\FileSystem;
use Exception;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FindByFileTypeCommand extends Command
{
    public const ARG_DIRECTORY = 'directory';

    public const OPT_TEXT_FILE = 'text';
    public const OPT_IMAGE_FILE = 'images';
    public const OPT_VIDEO_FILE = 'video';
    public const OPT_XML_FILE = 'xml';

    protected static $defaultName = 'find:by-file-type';

    protected static $defaultDescription = 'Find files by their genaral type.';

    public function __construct(
        private FileSystem $fileSystem
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
            )
            ->addOption(
                self::OPT_TEXT_FILE,
                null,
                InputOption::VALUE_NEGATABLE,
                'Filter by mime type "text/*"'
            )
            ->addOption(
                self::OPT_IMAGE_FILE,
                null,
                InputOption::VALUE_NEGATABLE,
                'Filter by mime type "image/*"'
            )
            ->addOption(
                self::OPT_VIDEO_FILE,
                null,
                InputOption::VALUE_NEGATABLE,
                'Filter by mime type "video/*"'
            )
            ->addOption(
                self::OPT_XML_FILE,
                null,
                InputOption::VALUE_NEGATABLE,
                'Filter by mime type "*/xml"'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stderr = $output instanceof ConsoleOutputInterface
                ? $output->getErrorOutput()
                : $output;
        
        $mimeTypeMatcher = new class ($input->getOptions()) {
            private const REGEX_TEMPLATE = '#(%s)#';

            private string $matchRegex;
            private string $dontMatchRegex;

            private array $match = [];
            private array $dontMatch = [];

            public function __construct(
                private array $options
            ) {
                foreach ($this->options as $option => $value) {
                    if (null === $value) {
                        // This means the option hasn't been set so do nothing.
                        continue;
                    }

                    match ($option) {
                        FindByFileTypeCommand::OPT_TEXT_FILE => $this->updateMatches('text', $value),
                        FindByFileTypeCommand::OPT_IMAGE_FILE => $this->updateMatches('image', $value),
                        FindByFileTypeCommand::OPT_VIDEO_FILE => $this->updateMatches('video', $value),
                        FindByFileTypeCommand::OPT_XML_FILE => $this->updateMatches('xml', $value),
                        default => null
                    };
                }

                $this->matchRegex = sprintf(self::REGEX_TEMPLATE, implode('|', $this->match));
                $this->dontMatchRegex = sprintf(self::REGEX_TEMPLATE, implode('|', $this->dontMatch));
            }

            public function mimeTypeMatches(string $mimeType): bool
            {
                $positivelyMatchedMimeType = preg_match($this->matchRegex, $mimeType);

                if (0 === $positivelyMatchedMimeType) {
                    // We've not matched the mime type.
                    // Return early to save the extra preg_match call.
                    return false;
                }
                
                $negativelyMatchedMimeType = preg_match($this->dontMatchRegex, $mimeType);

                return match (true) {
                    // We've matched the mime type and not requested to exclude it.
                    0 === $negativelyMatchedMimeType => true,
                    // We've matched the mime type and requested to exclude it.
                    1 === $positivelyMatchedMimeType && 1 === $negativelyMatchedMimeType => false,
                    // preg_match hasn't returned 0 or 1 so something has gone wrong.
                    default => throw new RuntimeException(
                        sprintf("Matching failed for mime type '%s'", $mimeType)
                    )
                };
            }

            private function updateMatches($pattern, $shouldMatch): void
            {
                match ($shouldMatch) {
                    true => $this->match[] = $pattern,
                    false => $this->dontMatch[] = $pattern
                };
            }
        };

        try {
            $directory = $input->getArgument(self::ARG_DIRECTORY);

            $stderr->write(sprintf("Finding files in '%s'\n\n", $directory));

            $files = $this->fileSystem->findFiles($directory);

            $progressBar = new ProgressBar($output);
            $progressBar->setFormat('verbose');

            $matchedFiles = [];
            foreach ($progressBar->iterate($files) as $file) {
                $mimeType = $file->getMimeType();

                if ($mimeTypeMatcher->mimeTypeMatches($mimeType)) {
                    $matchedFiles[] = $file;
                }
            }

            $stderr->write("\n");

            // TODO: improve the output options.
            foreach ($matchedFiles as $matchedFile) {
                $output->writeln($matchedFile->getPath());
            }

            $output->write("\n");

            return Command::SUCCESS;
        }
        catch (Exception $e) {
            $stderr->writeln($e->getMessage());

            return Command::FAILURE;
        }
    }
}