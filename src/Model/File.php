<?php declare(strict_types=1);

namespace BenRowan\PhotoTool\Model;

use BenRowan\PhotoTool\Service\Hasher;
use RuntimeException;
use Stringable;

class File implements Stringable
{
    /**
     * @var string|null
     */
    private $fullFileHash = null;
    /**
     * @var string[]
     */
    private $firstNByteHashes = [];
    /**
     * @var int|null
     */
    private $totalFileSizeBytes = null;
    /**
     * @var string|null
     */
    private $mimeType = null;
    
    /**
     * @param Hasher $hasher Service class used for hashing.
     * @param string $path Path to the file on the filesystem.
     */
    public function __construct(
        private Hasher $hasher,
        private string $path
    ) {
        if (!file_exists($this->getPath())) {
            throw new RuntimeException(
                "Trying to work with file '{$this->getPath()}' but it doesn't exist"
            );
        }

        if (!is_readable($this->getPath())) {
            throw new RuntimeException(
                "Trying to work with file '{$this->getPath()}' but it isn't readable"
            );
        }
    }

    /**
     * Get the path to the file on the filesystem.
     * 
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Detect the mime type of the file.
     * 
     * Note: This is cached so it's safe to call multiple times.
     *
     * @return string
     */
    public function getMimeType(): string
    {
        if (null === $this->mimeType) {
            // Note: turned out to be too slow :)
            //
            // // Using this over mime_content_type or Fileinfo as
            // // people seem to get mixed results with those.

            // $this->mimeType = shell_exec(
            //     sprintf("file -b --mime-type -m /usr/share/misc/magic '%s'", $this->getPath())
            // );

            $this->mimeType = mime_content_type($this->getPath());
        }
        
        return $this->mimeType;
    }

    /**
     * Get a hash of the full file.
     * 
     * Note: This is cached so it's safe to call multiple times.
     * 
     * @return string
     */
    public function hashFullFile(): string
    {
        if (null === $this->fullFileHash) {
            $allBytes = $this->readFirstNBytes($this->getTotalFilesizeBytes());

            $this->fullFileHash = $this->hasher->hash($allBytes);
        }
        
        return $this->fullFileHash;
    }

    /**
     * Get a hash of first N bytes of the file.
     * 
     * Note: This is cached so it's safe to call multiple times.
     * 
     * @param integer $numBytes The number of bytes to read from the start of the file.
     * 
     * @return string
     */
    public function hashFirstNBytes(int $numBytes): string
    {
        if (!isset($this->firstNByteHashes[$numBytes])) {
            $firstNBytes = $this->readFirstNBytes($numBytes);

            $this->firstNByteHashes[$numBytes] = $this->hasher->hash($firstNBytes);
        }
        
        return $this->firstNByteHashes[$numBytes];
    }

    /**
     * Get the total size of the file in bytes.
     * 
     * Note: This is cached so it's safe to call multiple times.
     * 
     * @return int
     */
    public function getTotalFileSizeBytes(): int
    {
        if (null === $this->totalFileSizeBytes) {
            $totalFileSizeBytes = filesize($this->getPath());

            if (false === $totalFileSizeBytes) {
                throw new RuntimeException(
                    "Unable to get filesize for '{$this->getPath()}'"
                );
            }

            $this->totalFileSizeBytes = $totalFileSizeBytes;
        }


        return $this->totalFileSizeBytes;
    }

    public function __toString(): string
    {
        return $this->getPath();
    }

    /**
     * Read the first $numBytes bytes from this file.
     * 
     * @param int $numBytes Number of bytes to read.
     * 
     * @return string
     */
    private function readFirstNBytes(int $numBytes): string
    {
        if ($numBytes < 0) {
            throw new RuntimeException(
                "Can't read a negative number of bytes from '{$this->getPath()}'"
            );
        }

        $handle = $this->createHandle();
        $bytes = fread($handle, $numBytes);
        $this->closeHandle($handle);

        if (false === $bytes) {
            throw new RuntimeException(
                "Failed to read first '$numBytes' bytes from '{$this->getPath()}'"
            );
        }

        return $bytes;
    }

    /**
     * Create a file handle for this file.
     * 
     * @return resource
     */
    private function createHandle()
    {
        $handle = fopen($this->getPath(), 'rb');

        if (false === $handle) {
            throw new RuntimeException("Unable to open '{$this->getPath()}'");
        }

        return $handle;
    }

    /**
     * Close the provided file handle.
     * 
     * @param mixed $handle
     * 
     * @return void
     */
    private function closeHandle($handle): void
    {
        if (!is_resource($handle)) {
            $type = gettype($handle);

            throw new RuntimeException(
                "Only resources can be closed. Given '$type' for file '{$this->getPath()}'"
            );
        }

        if (false === fclose($handle)) {
            throw new RuntimeException("Unable to close '{$this->getPath()}'");
        }
    }
}