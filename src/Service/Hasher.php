<?php declare(strict_types=1);

namespace BenRowan\PhotoTool\Service;

use RuntimeException;

/**
 * Encapsulates all hashing logic for the tool.
 * 
 * Hash algorithms were selected for speed and minimal collisions.
 * For our usecase, we don't need cryptographically secure hashes.
 * 
 * https://php.watch/articles/php-hash-benchmark
 */
class Hasher
{
    private const HASH_ALGO_XXH128 = 'xxh128';
    private const HASH_ALGO_MURMUR3F = 'murmur3f';
    private const HASH_ALGO_MD5 = 'md5';

    private $algorithm = null;

    public function __construct()
    {
        $algos = hash_algos();

        $this->algorithm = match (true) {
            // https://php.watch/versions/8.1/xxHash
            false !== array_search(self::HASH_ALGO_XXH128, $algos) => self::HASH_ALGO_XXH128,
            // https://php.watch/versions/8.1/MurmurHash3
            false !== array_search(self::HASH_ALGO_MURMUR3F, $algos) => self::HASH_ALGO_MURMUR3F,
            false !== array_search(self::HASH_ALGO_MD5, $algos) => self::HASH_ALGO_MD5,
            default => throw new RuntimeException(sprintf(
                "Unable to find supported hashing algorithm. Checked %s, %s & %s\n",
                self::HASH_ALGO_XXH128,
                self::HASH_ALGO_MURMUR3F,
                self::HASH_ALGO_MD5
            ))
        };
    }

    public function hash(string $subject): string
    {
        return hash($this->algorithm, $subject);
    }
}