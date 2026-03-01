<?php

/*
# The overall stats:

$ find ~/Pictures/ -type f | wc -l
43,059

$ du -sh ~/Pictures/
282G

# Collection of JPEGs:

$ find ~/Pictures/<SUB FOLDER> -type f | wc -l
914

$ du -sh ~/Pictures/<SUB FOLDER>
2.4G

$ php -d memory_limit=8G scratch/file_hash_benchmark.php ~/Pictures/<SUB FOLDER>
Processing files in '/home/ben/Pictures/<SUB FOLDER>'
Found '80' files

Benchmarking MD5 hash (128 bit) - full file:
Run 1 took '0.6368' seconds
Run 2 took '0.3966' seconds
Run 3 took '0.3959' seconds
Run 4 took '0.3962' seconds
Run 5 took '0.3968' seconds
Average run time was '0.4445' seconds

Benchmarking Murmur3F hash (128bit) - full file:
Run 1 took '0.0622' seconds
Run 2 took '0.0620' seconds
Run 3 took '0.0626' seconds
Run 4 took '0.0634' seconds
Run 5 took '0.0631' seconds
Average run time was '0.0626' seconds

Benchmarking xxHash hash (128 bit) - full file:
Run 1 took '0.0481' seconds
Run 2 took '0.0478' seconds
Run 3 took '0.0478' seconds
Run 4 took '0.0476' seconds
Run 5 took '0.0470' seconds
Average run time was '0.0476' seconds

Benchmarking xxHash hash (128 bit) - first 500 bytes:
Run 1 took '0.0005' seconds
Run 2 took '0.0004' seconds
Run 3 took '0.0004' seconds
Run 4 took '0.0004' seconds
Run 5 took '0.0004' seconds
Average run time was '0.0004' seconds

# Collection of RAW files:

$ find ~/Pictures/<SUB FOLDER> -type f | wc -l
882

$ du -sh ~/Pictures/<SUB FOLDER>
8.3G

$ php -d memory_limit=9G scratch/file_hash_benchmark.php ~/Pictures/G15/
Processing files in '/home/ben/Pictures/G15/'
Found '80' files

Benchmarking MD5 hash (128 bit) - full file:
Run 1 took '4.1223' seconds
Run 2 took '3.3859' seconds
Run 3 took '3.3986' seconds
Run 4 took '3.3988' seconds
Run 5 took '3.4011' seconds
Average run time was '3.5413' seconds

Benchmarking Murmur3F hash (128bit) - full file:
Run 1 took '0.7132' seconds
Run 2 took '0.7017' seconds
Run 3 took '0.6987' seconds
Run 4 took '0.7333' seconds
Run 5 took '0.7302' seconds
Average run time was '0.7154' seconds

Benchmarking xxHash hash (128 bit) - full file:
Run 1 took '0.5906' seconds
Run 2 took '0.5902' seconds
Run 3 took '0.5908' seconds
Run 4 took '0.6131' seconds
Run 5 took '0.5879' seconds
Average run time was '0.5945' seconds

Benchmarking xxHash hash (128 bit) - first 500 bytes:
Run 1 took '0.0005' seconds
Run 2 took '0.0004' seconds
Run 3 took '0.0004' seconds
Run 4 took '0.0004' seconds
Run 5 took '0.0004' seconds
Average run time was '0.0004' seconds

# Collection of video files:

$ find ~/Pictures/<SUB FOLDER> -type f | wc -l
80

$ du -sh ~/Pictures/<SUB FOLDER>
7.9G

$ php -d memory_limit=8G scratch/file_hash_benchmark.php ~/Pictures/<SUB FOLDER>
Processing files in '/home/ben/Pictures/<SUB FOLDER>'
Found '80' files

Benchmarking MD5 hash (128 bit) - full file:
Run 1 took '21.7217' seconds
Run 2 took '21.7138' seconds
Run 3 took '21.7074' seconds
Run 4 took '21.6748' seconds
Run 5 took '21.7492' seconds
Average run time was '21.7134' seconds

Benchmarking Murmur3F hash (128bit) - full file:
Run 1 took '4.4586' seconds
Run 2 took '4.4651' seconds
Run 3 took '4.4914' seconds
Run 4 took '4.4608' seconds
Run 5 took '4.4624' seconds
Average run time was '4.4677' seconds

Benchmarking xxHash hash (128 bit) - full file:
Run 1 took '3.8149' seconds
Run 2 took '3.7608' seconds
Run 3 took '3.7487' seconds
Run 4 took '3.7347' seconds
Run 5 took '3.7446' seconds
Average run time was '3.7607' seconds

Benchmarking xxHash hash (128 bit) - first 500 bytes:
Run 1 took '0.0006' seconds
Run 2 took '0.0004' seconds
Run 3 took '0.0004' seconds
Run 4 took '0.0004' seconds
Run 5 took '0.0004' seconds
Average run time was '0.0004' seconds

So What Does This Tell Us?
==========================

Total number of files:	43059
Total size of files (GB):	282
	
xxHash – JPEG – full file	
Total size of files (GB):	2.40
Times this goes into total size (GB):	117.50
Average time (seconds):	0.0476
Total processing time if all files were JPEGs (seconds):	5.59
Total processing time if all files were JPEGs (minutes):	0.09
	
xxHash – RAW – full file	
Total size of files (GB):	8.30
Times this goes into total size (GB):	33.98
Average time (seconds):	0.5945
Total processing time if all files were JPEGs (seconds):	20.20
Total processing time if all files were JPEGs (minutes):	0.34
	
xxHash – VIDEO – full file	
Total size of files (GB):	7.90
Times this goes into total size (GB):	35.70
Average time (seconds):	3.7607
Total processing time if all files were JPEGs (seconds):	134.24
Total processing time if all files were JPEGs (minutes):	2.24
	
xxHash – JPEG – first 500 bytes	
Total size of files (GB):	2.40
Times this goes into total size (GB):	117.50
Average time (seconds):	0.0004
Total processing time if all files were JPEGs (seconds):	0.05
Total processing time if all files were JPEGs (minutes):	0.00
	
xxHash – RAW – first 500 bytes	
Total size of files (GB):	8.30
Times this goes into total size (GB):	33.98
Average time (seconds):	0.0004
Total processing time if all files were JPEGs (seconds):	0.01
Total processing time if all files were JPEGs (minutes):	0.00
	
xxHash – VIDEO – first 500 bytes	
Total size of files (GB):	7.90
Times this goes into total size (GB):	35.70
Average time (seconds):	0.0004
Total processing time if all files were JPEGs (seconds):	0.01
Total processing time if all files were JPEGs (minutes):	0.00

From this we can see:
- In the worst case where all files are videos and we hash the full file, we should be able to complete this in 2.24 minutes.
- When we just hash the first 500 bytes, the run time isn't impacted by file size (as expected).
- Even vs the best case for full file hashing, hashing the first 500 bytes is 559 times faster.
*/

const HASH_ALGO_XXH128 = 'xxh128';
const HASH_ALGO_MURMUR3F = 'murmur3f';
const HASH_ALGO_MD5 = 'md5';

function runBenchmark(callable $benchmark, int $iterations=5)
{
    $cumulativeTotalTime = 0;

    for ($i=1; $i<=$iterations; $i++) {
        
        $startTime = microtime(true);
        $benchmark();
        $endTime = microtime(true);

        $totalTime = $endTime - $startTime;
        $cumulativeTotalTime += $totalTime;

        echo sprintf("Run %d took '%s' seconds\n", $i, number_format($totalTime, 4));
    }

    $avgTime = $cumulativeTotalTime / $iterations;

    echo sprintf("Average run time was '%s' seconds\n\n", number_format($avgTime, 4));
}

function findFiles(string $directory, &$files = [])
{
    foreach (scandir($directory) as $filename) {
        if (count($files) >= 80) {
            return $files;
        }

        if (in_array($filename, ['.', '..'])) {
            continue;
        }

        $path = realpath($directory . DIRECTORY_SEPARATOR . $filename);
        
        if (is_dir($path)) {
            findFiles($path, $files);
        } else {
            $files[] = $path;
        }
    }

    return $files;
};

echo "Processing files in '{$argv[1]}'\n";

$files = findFiles($argv[1]);

echo sprintf("Found '%s' files\n\n", count($files));

// ------------------------------------

echo "Benchmarking MD5 hash (128 bit) - full file:\n";
$data = runBenchmark(function () use ($files) {
    $hashes = [];
    foreach ($files as $file) {
        $contents = file_get_contents($file);
        $hashes[] = hash(HASH_ALGO_MD5, $contents);
    }

    // Trying to ensure the hash call isn't optimised away
    return ['hashes' => $hashes];
});

// ------------------------------------

echo "Benchmarking Murmur3F hash (128bit) - full file:\n";
$data = runBenchmark(function () use ($files) {
    $hashes = [];
    foreach ($files as $file) {
        $contents = file_get_contents($file);
        $hashes[] = hash(HASH_ALGO_MURMUR3F, $contents);
    }

    // Trying to ensure the hash call isn't optimised away
    return ['hashes' => $hashes];
});

// ------------------------------------

echo "Benchmarking xxHash hash (128 bit) - full file:\n";
$data = runBenchmark(function () use ($files) {
    $hashes = [];
    foreach ($files as $file) {
        $contents = file_get_contents($file);
        $hashes[] = hash(HASH_ALGO_XXH128, $contents);
    }

    // Trying to ensure the hash call isn't optimised away
    return ['hashes' => $hashes];
});

// ------------------------------------

// I'm just going to bench xxHash from here as it's by far the fastest.

echo "Benchmarking xxHash hash (128 bit) - first 500 bytes:\n";
$data = runBenchmark(function () use ($files) {
    $hashes = [];
    foreach ($files as $file) {
        $handle = fopen($file, 'rb');
        $bytes = fread($handle, 500);
        fclose($handle);
        
        $hashes[] = hash(HASH_ALGO_XXH128, $bytes);
    }

    // Trying to ensure the hash call isn't optimised away
    return ['hashes' => $hashes];
});