<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'parse:filenames',
    description: 'Parse filenames for #numbers, write them to a txt file, and prefix filenames with the first number.'
)]
final class FilenameParseCommand extends Command
{
    #[\Override]
    protected function configure(): void
    {
        $this
            ->addArgument('folder', InputArgument::OPTIONAL, 'Folder path to scan')
            ->addArgument('output', InputArgument::OPTIONAL, 'Output txt file path');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $folderPath = (string) ($input->getArgument('folder') ?: $io->ask('Folder path to scan', \getcwd()));

        $action = $io->choice(
            'What do you want to do?',
            ['extract numbers', 'rename files', 'both'],
            'both'
        );

        $patternInput = (string) $io->ask('Pattern to search (regex)', '#(\d+)');
        $pattern      = $this->normalizePattern($patternInput);

        $outputPath = '';
        if ('extract numbers' === $action || 'both' === $action) {
            $outputPath = (string) ($input->getArgument('output') ?: $io->ask('Output txt file path', \getcwd() . DIRECTORY_SEPARATOR . 'numbers.txt'));
        }

        if (!\is_dir($folderPath)) {
            $io->error("Folder not found: {$folderPath}");

            return Command::FAILURE;
        }

        $handle = \opendir($folderPath);
        if (false === $handle) {
            $io->error("Unable to open folder: {$folderPath}");

            return Command::FAILURE;
        }

        $numbers      = [];
        $renamedCount = 0;
        $skippedCount = 0;

        while (($entry = \readdir($handle)) !== false) {
            if ('.' === $entry || '..' === $entry) {
                continue;
            }

            $fullPath = $folderPath . DIRECTORY_SEPARATOR . $entry;
            if (!\is_file($fullPath)) {
                continue;
            }

            if (\preg_match_all($pattern, $entry, $matches)) {
                if ('extract numbers' === $action || 'both' === $action) {
                    foreach ($matches[1] as $num) {
                        $numbers[] = $num;
                    }
                }

                if ('rename files' === $action || 'both' === $action) {
                    $firstMatch = $matches[1][0] ?? $matches[0][0];
                    $pathInfo   = \pathinfo($entry);
                    $baseName   = (string) $pathInfo['filename'];
                    $extension  = isset($pathInfo['extension']) ? ('.' . $pathInfo['extension']) : '';

                    $cleanBase = \trim($baseName);
                    $cleanBase = \trim($cleanBase, " ._-\t\n\r\0\x0B");

                    $newBase = $firstMatch . '.' . ('' !== $cleanBase ? $cleanBase : '');
                    $newName = $newBase . $extension;

                    if ($newName !== $entry) {
                        $newPath = $folderPath . DIRECTORY_SEPARATOR . $newName;
                        if (!\file_exists($newPath)) {
                            if (!\rename($fullPath, $newPath)) {
                                $io->warning("Failed to rename: {$entry}");
                            } else {
                                ++$renamedCount;
                            }
                        } else {
                            $io->warning("Skip rename (target exists): {$newName}");
                            ++$skippedCount;
                        }
                    }
                }
            }
        }

        \closedir($handle);

        if ('extract numbers' === $action || 'both' === $action) {
            if ([] === $numbers) {
                \file_put_contents($outputPath, '');
                $io->success('No matches found. Output file created empty.');

                return Command::SUCCESS;
            }

            \sort($numbers, SORT_NUMERIC);

            $content = \implode(PHP_EOL, $numbers) . PHP_EOL;
            \file_put_contents($outputPath, $content);
        }

        $io->success(\sprintf('Done. Numbers: %d. Renamed: %d. Skipped: %d.%s', \count($numbers), $renamedCount, $skippedCount, '' !== $outputPath ? " Output: {$outputPath}" : ''));

        return Command::SUCCESS;
    }

    private function normalizePattern(string $pattern): string
    {
        $pattern = \trim($pattern);
        if ('' === $pattern) {
            return '/#(\d+)/';
        }

        $first       = $pattern[0];
        $last        = $pattern[\strlen($pattern) - 1];
        $isDelimited = $first === $last && !\ctype_alnum($first) && '\\' !== $first;

        return $isDelimited ? $pattern : '/' . $pattern . '/';
    }
}
