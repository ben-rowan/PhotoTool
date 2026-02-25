<?php

namespace BenRowan\ManagePhotos\Service;

use BenRowan\ManagePhotos\Model\File;

class Filesystem
{
    /**
     * Get all files from all directories recursively starting at $directory.
     * 
     * Note: scandir was used in place of RecursiveDirectoryIterator as it's
     * twice as fast (for what we need) and uses much less memory:
     * 
     * $ php -d memory_limit=500M scratch/directory_traversal_scandir.php <directory>
     * Runtime (seconds): 0.16
     * Memory usage (MB): 3.63
     * Number of files found: 23,055
     * 
     * $ php -d memory_limit=500M scratch/directory_traversal_iterator.php <directory>
     * Runtime (seconds): 0.23
     * Memory usage (MB): 193.44
     * Number of files found: 23,055
     * 
     * @param string $directory The directory to start from.
     * @param File[] $files The collected files. 
     * 
     * @return File[]
     */
    public function getFilesRecursively(string $directory, &$files = [])
    {
        foreach (scandir($directory) as $filename) {
            if (in_array($filename, ['.', '..'])) {
                continue;
            }
    
            $path = realpath($directory . DIRECTORY_SEPARATOR . $filename);
            
            if (is_dir($path)) {
                $this->getFilesRecursively($path, $files);
            } else {
                $files[] = new File($path);
            }
        }
    
        return $files;
    }
}