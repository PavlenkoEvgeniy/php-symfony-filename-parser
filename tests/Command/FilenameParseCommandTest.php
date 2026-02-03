<?php

declare(strict_types=1);

namespace Tests\Command;

use App\Command\FilenameParseCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class FilenameParseCommandTest extends TestCase
{
    /** @var string[] */
    private array $tempDirs = [];

    protected function tearDown(): void
    {
        foreach ($this->tempDirs as $dir) {
            $this->deleteDir($dir);
        }
        $this->tempDirs = [];
    }

    public function testExtractNumbersWritesSortedOutput(): void
    {
        $dir = $this->createTempDir();
        $this->writeFile($dir, 'report #2 final.pdf');
        $this->writeFile($dir, 'img #10.png');
        $this->writeFile($dir, 'note #3 #1.txt');

        $outputPath = $dir . DIRECTORY_SEPARATOR . 'numbers.txt';

        $tester = new CommandTester(new FilenameParseCommand());
        $tester->setInputs(['extract numbers', '#(\d+)']);
        $tester->execute([
            'folder' => $dir,
            'output' => $outputPath,
        ], ['interactive' => true]);

        $this->assertFileExists($outputPath);
        $content = \file_get_contents($outputPath);
        $this->assertSame("1\n2\n3\n10\n", $content);
    }

    public function testRenameFilesPrefixesFirstMatch(): void
    {
        $dir = $this->createTempDir();
        $this->writeFile($dir, 'report #2 final.pdf');

        $tester = new CommandTester(new FilenameParseCommand());
        $tester->setInputs(['rename files', '#(\d+)']);
        $tester->execute([
            'folder' => $dir,
        ], ['interactive' => true]);

        $this->assertFileDoesNotExist($dir . DIRECTORY_SEPARATOR . 'report #2 final.pdf');
        $this->assertFileExists($dir . DIRECTORY_SEPARATOR . '2.report #2 final.pdf');
    }

    public function testBothExtractsAndRenames(): void
    {
        $dir = $this->createTempDir();
        $this->writeFile($dir, 'abc #3 and #5.txt');

        $outputPath = $dir . DIRECTORY_SEPARATOR . 'numbers.txt';

        $tester = new CommandTester(new FilenameParseCommand());
        $tester->setInputs(['both', '#(\d+)']);
        $tester->execute([
            'folder' => $dir,
            'output' => $outputPath,
        ], ['interactive' => true]);

        $this->assertFileExists($dir . DIRECTORY_SEPARATOR . '3.abc #3 and #5.txt');
        $this->assertFileExists($outputPath);
        $content = \file_get_contents($outputPath);
        $this->assertSame("3\n5\n", $content);
    }

    private function createTempDir(): string
    {
        $dir = \sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'filename-parser-' . \bin2hex(\random_bytes(6));
        \mkdir($dir);
        $this->tempDirs[] = $dir;

        return $dir;
    }

    private function writeFile(string $dir, string $name, string $contents = ''): void
    {
        \file_put_contents($dir . DIRECTORY_SEPARATOR . $name, $contents);
    }

    private function deleteDir(string $dir): void
    {
        if (!\is_dir($dir)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                \rmdir($item->getPathname());
            } else {
                \unlink($item->getPathname());
            }
        }

        \rmdir($dir);
    }
}
