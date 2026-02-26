<?php

namespace BenRowan\PhotoTool\Service;

use BenRowan\PhotoTool\Model\File;

class FileSystem
{
    /**
     * Find all files from all directories recursively starting at $directory.
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
     * @param string $directory The directory to start searching from.
     * @param File[] $files The files found. 
     * 
     * @return File[]
     */
    public function findFiles(string $directory, &$files = [])
    {
        foreach (scandir($directory) as $filename) {
            if (in_array($filename, ['.', '..'])) {
                continue;
            }
    
            $path = realpath($directory . DIRECTORY_SEPARATOR . $filename);
            
            if (is_dir($path)) {
                $this->findFiles($path, $files);
            } else {
                $files[] = new File($path);
            }
        }
    
        return $files;
    }
}