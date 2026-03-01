<?php declare(strict_types=1);

namespace BenRowan\PhotoTool\Test\Model\File;

use BenRowan\PhotoTool\Model\Duplicates;
use BenRowan\PhotoTool\Model\File;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DuplicatesTest extends TestCase
{
    /**
     * @var Duplicates
     */
    private $subject;

    public function setUp(): void
    {
        $this->subject = new Duplicates();
    }

    #[Test]
    public function shouldSplitFilesIntoCollectionsBasedOnKey()
    {
        $fileStub1 = $this->createFileStub('some/path/file1.jpg');
        $fileStub2 = $this->createFileStub('some/path/file2.jpg');
        $fileStub3 = $this->createFileStub('some/path/file3.jpg');
        $fileStub4 = $this->createFileStub('some/path/file4.jpg');
        $fileStub5 = $this->createFileStub('some/path/file5.jpg');

        $input = [
            '1' => [
                $fileStub1,
                $fileStub2,
            ],
            '2' => [
                $fileStub3,
                $fileStub4,
            ],
            '3' => [
                $fileStub5,
            ]
        ];

        $expected = array_values($input);

        foreach ($input as $key => $files) {
            foreach ($files as $file) {
                $this->subject->addFile((string) $key, $file);
            }
        }

        $this->assertSame($expected, $this->subject->getDuplicates());
    }

    #[Test]
    public function shouldRemoveUniqueFilesWhenPruneIsCalled()
    {
        $fileStub1 = $this->createFileStub('some/path/file1.jpg');
        $fileStub2 = $this->createFileStub('some/path/file2.jpg');
        $fileStub3 = $this->createFileStub('some/path/file3.jpg');
        $fileStub4 = $this->createFileStub('some/path/file4.jpg');
        $fileStub5 = $this->createFileStub('some/path/file5.jpg');

        $input = [
            '1' => [
                $fileStub1,
                $fileStub2,
            ],
            '2' => [
                $fileStub3,
            ],
            '3' => [
                $fileStub4,
            ],
            '4' => [
                $fileStub5,
            ]
        ];

        $expected = [
            [
                $fileStub1,
                $fileStub2,
            ]
        ];

        foreach ($input as $key => $files) {
            foreach ($files as $file) {
                $this->subject->addFile((string) $key, $file);
            }
        }

        $this->subject->pruneUniqueFiles();

        $this->assertSame($expected, $this->subject->getDuplicates());
    }

    #[Test]
    public function shouldFlattenCollectionsArrayWhenGetAllFilesIsCalled()
    {
        $fileStub1 = $this->createFileStub('some/path/file1.jpg');
        $fileStub2 = $this->createFileStub('some/path/file2.jpg');
        $fileStub3 = $this->createFileStub('some/path/file3.jpg');
        $fileStub4 = $this->createFileStub('some/path/file4.jpg');

        $input = [
            '1' => [
                $fileStub1,
                $fileStub2,
            ],
            '2' => [
                $fileStub3,
                $fileStub4,
            ]
        ];

        $expected = [
            $fileStub1,
            $fileStub2,
            $fileStub3,
            $fileStub4,
        ];

        foreach ($input as $key => $files) {
            foreach ($files as $file) {
                $this->subject->addFile((string) $key, $file);
            }
        }

        $this->assertSame($expected, $this->subject->getAllFiles());
    }

    private function createFileStub(string $path): File
    {
        $stub = $this->getStubBuilder(File::class)
            ->disableOriginalConstructor()
            ->getStub();

        $stub->method('getPath')
            ->willReturn($path);

        return $stub;
    }
}