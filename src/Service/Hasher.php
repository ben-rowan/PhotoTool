<?php

namespace BenRowan\PhotoTool\Service;

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

    private $hashAlgorithm = null;

    public function __construct()
    {
        $availableAlgorithms = hash_algos();

        // https://php.watch/versions/8.1/xxHash
        if (false !== array_search(self::HASH_ALGO_XXH128, $availableAlgorithms)) {
            $this->hashAlgorithm = self::HASH_ALGO_XXH128;
        }
        // https://php.watch/versions/8.1/MurmurHash3
        else if (false !== array_search(self::HASH_ALGO_MURMUR3F, $availableAlgorithms)) {
            $this->hashAlgorithm = self::HASH_ALGO_MURMUR3F;
        }
        else if (false !== array_search(self::HASH_ALGO_MD5, $availableAlgorithms)) {
            $this->hashAlgorithm = self::HASH_ALGO_MD5;
        }
        else {
            throw new \RuntimeException(printf(
                "Unable to find supported hashing algorithm. Checked %s, %s & %s\n",
                self::HASH_ALGO_XXH128,
                self::HASH_ALGO_MURMUR3F,
                self::HASH_ALGO_MD5
            ));
        }
    }

    public function hash(string $subject): string
    {
        return hash($this->hashAlgorithm, $subject);
    }
}