<?php

namespace BenRowan\PhotoTool\Model;

use RuntimeException;

/**
 * Class File
 * 
 * @package BenRowan\PhotoTool\Model
 */
class File
{
    /**
     * @var string
     */
    private $path;
    /**
     * @var string|null
     */
    private $fullFileHash = null;
    /**
     * @var String[]
     */
    private $firstNByteHashes = [];
    /**
     * @var int|null
     */
    private $totalFilesizeBytes = null;
    
    /**
     * @param string $path Path to the file on the filesystem.
     */
    public function __construct(string $path)
    {
        $this->path = $path;

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

            $this->fullFileHash = md5($allBytes);
        }
        
        return $this->fullFileHash;
    }

    /**
     * Get a hash of first N bytes of the file.
     * 
     * Note: This is cached so it's safe to call multiple times.
     * 
     * @return string
     */
    public function hashFirstNBytes(int $numBytes): string
    {
        if (!isset($this->firstNByteHashes[$numBytes])) {
            $firstNBytes = $this->readFirstNBytes($numBytes);

            $this->firstNByteHashes[$numBytes] = md5($firstNBytes);
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
    public function getTotalFilesizeBytes(): int
    {
        if (null === $this->totalFilesizeBytes) {
            $totalFilesizeBytes = filesize($this->getPath());

            if (false === $totalFilesizeBytes) {
                throw new RuntimeException(
                    "Unable to get filesize for '{$this->getPath()}'"
                );
            }

            $this->totalFilesizeBytes = $totalFilesizeBytes;
        }


        return $this->totalFilesizeBytes;
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