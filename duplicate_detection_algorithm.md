Create root node.

[root]

Find all files
FOREACH file
    Fetch file size (bytes)
    IF root node has no node for filesize, add node
    Add file path to path array held on associated filesize node

[root]
|
|---[100]
|   |---<some/path/file1.jpg>
|   |---<some/other/path/file1.jpg>
|
|---[20]
|   |---<some/path/file2.jpg>
|   |---<some/other/path/file2.jpg>
|
|---[10]
    |---<some/path/file3.png>

Prune nodes - remove any filesize nodes with only 1 file in their file path array

[root]
|
|---[100]
|   |---<some/path/file1.jpg>
|   |---<some/other/path/file1.jpg>
|
|---[20]
    |---<some/path/file2.jpg>
    |---<some/other/path/file2.jpg>

FOREACH path held on a filesize node
    Fetch first N bytes of file
    Hash N bytes
    IF filesize node has no node for first N bytes hash, add node
    Add file path to path array held on associated first N bytes node

[root]
|
|---[100]
|   |---[N_HASH_1]
|   |   |---<some/path/file1.jpg>
|   |
|   |---[N_HASH_2]
|       |---<some/other/path/file1.jpg>
|
|---[20]
    |---[N_HASH_3]
        |---<some/path/file2.jpg>
        |---<some/other/path/file2.jpg>

Prune nodes - remove any first N Byte nodes with only 1 file in their file path array

[root]
|
|---[20]
    |---[N_HASH_3]
        |---<some/path/file2.jpg>
        |---<some/other/path/file2.jpg>

FOREACH path held on a first N bytes node
    Fetch all bytes for file
    Hash bytes
    IF first N bytes node has no node for full hash, add node
    Add file path to path array held on associated full hash node

[root]
|
|---[20]
    |---[N_HASH_3]
        |---[FULL_HASH_1]
            |---<some/path/file2.jpg>
            |---<some/other/path/file2.jpg>


Any full hash node containing > 1 files represents a duplicate.