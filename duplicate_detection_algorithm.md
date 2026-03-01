Plan / Algorithm For Finding Duplicate Files
============================================

Find all files recursively from target directory and instantiate a File object for each.

Sort By File Size
-----------------

> We sort by file size first as this is a relatively quick operation.
> I can scan ~50k files (~300GB) in less than one second on my machine.

```
FOREACH File:
    FETCH file size (bytes).
    IF array has nothing set for file size key, add an empty array.
    ADD File to array associated with file size key.
```

This leaves us with:

```php
[
    '100' => [
        File("some/path/file1.jpg"),
        File("some/other/path/file1.jpg"),
    ],
    '20' => [
        File("some/path/file2.jpg"),
        File("some/other/path/file2.jpg"),
    ],
    '10' => [
        File("some/path/file3.png")
    ],
]
```

Remove any arrays with only one file:

```php
[
    '100' => [
        File("some/path/file1.jpg"),
        File("some/other/path/file1.jpg"),
    ],
    '20' => [
        File("some/path/file2.jpg"),
        File("some/other/path/file2.jpg"),
    ],
]
```

We're now left with a set of files that _could_ be duplicates.

Flatten the array ready for the next step:

```php
[
    File("some/path/file1.jpg"),
    File("some/other/path/file1.jpg"),
    File("some/path/file2.jpg"),
    File("some/other/path/file2.jpg"),
]
```

Ok, so this algorithm is going to be repeated a number of times, so let's extract it into a "function":

```
FUNCTION sortFiles(callable $sortingKeyGenerator, array $files):
    INIT $collections array for us to store sorted Files.

    FOREACH File in $files:    
        GENERATE $sortingKey using $sortingKeyGenerator(File).
        IF $collections has nothing set for $sortingKey:
            SET empty array.
        ADD File to array associated with $sortingKey.

    FOREACH $collection in $collections:
        IF $collection SIZE IS 1:
            REMOVE $collection from $collections.

    // The array flattening will happen outside this function
    // as we want the unflattened array on the final call.
    RETURN $collections.
END
```

Sort By First N Bytes
---------------------

In order to have a high confidence we've found a duplicate, we need to read the full file from disk and hash it.

Based on the benchmarking I completed, you can see that full file hashing speed is impacted by file type (see [file_hash_benchmark.php](scratch/file_hash_benchmark.php)). However, only reading the first N bytes of a file isn't (in a meaningful way) and is increadibly fast. As such, we'll introduce a "first N bytes" sorting step here to reduce the search space for full file hashing.

```
CALLABLE sortingKeyGenerator(File):
    FETCH first N bytes of file.
    HASH first N bytes of file.

    RETURN hash.

CALL sortFiles($sortingKeyGenerator, flattened Files array from previous step).
```

Sort By Full File Hash
----------------------

We've now done our best to reduce the search space, so it's time to do the most expensive operation, full file hashing.

```
CALLABLE sortingKeyGenerator(File):
    FETCH all bytes from file.
    HASH all bytes from file.

    RETURN hash.

CALL sortFiles($sortingKeyGenerator, flattened Files array from previous step).
```

The output of sortFiles should this time only contain duplicates grouped together into "collections".
