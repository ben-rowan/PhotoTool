<?php declare(strict_types=1);

namespace BenRowan\PhotoTool\Model;

class Duplicates
{
    /**
     * [
     *     'sorting_key_1' => [
     *         File1,
     *         File2,
     *     ],
     *     'sorting_key_2' => [
     *         File3,
     *         File4,
     *         File5,
     *     ],
     * ]
     * 
     * @var array[]
     */
    private array $collections = [];

    /**
     * Add a file for duplicate detection.
     *
     * @param string $key The value you'd like to sort the file by.
     * @param File $file The file object to be sorted.
     * 
     * @return void
     */
    public function addFile(string $key, File $file): void
    {
        if (!isset($this->collections[$key])) {
            $this->collections[$key] = [];
        }

        $this->collections[$key][] = $file;
    }

    /**
     * Remove any unique files.
     * 
     * This should be called after all files have
     * been added / sorted to leave only duplicate files.
     *
     * @return void
     */
    public function pruneUniqueFiles(): void
    {
        $this->collections = array_filter(
            $this->collections,
            function (array $collection) {
                return count($collection) > 1;
            }
        );
    }

    /**
     * Get's all the files in a flat array.
     * 
     * [
     *     File1,
     *     File2,
     *     File3,
     *     File4,
     *     File5,
     * ]
     *
     * @return array
     */
    public function getAllFiles(): array
    {
        return array_merge(...$this->getDuplicates());
    }

    /**
     * Get's all the files in their sorted collections.
     * 
     * [
     *     // These potentially duplicate each other:
     *     [
     *         File1,
     *         File2,
     *     ],
     *     // And these potentially duplicate each other:
     *     [
     *         File3,
     *         File4,
     *         File5,
     *     ]
     * ]
     *
     * @return array
     */
    public function getDuplicates(): array
    {
        return array_values($this->collections);
    }
}