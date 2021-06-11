<?php

declare (strict_types=1);
namespace ConfigTransformer202106117\Symplify\SmartFileSystem;

use ConfigTransformer202106117\Symplify\SmartFileSystem\Exception\DirectoryNotFoundException;
use ConfigTransformer202106117\Symplify\SmartFileSystem\Exception\FileNotFoundException;
final class FileSystemGuard
{
    public function ensureFileExists(string $file, string $location) : void
    {
        if (\file_exists($file)) {
            return;
        }
        throw new \ConfigTransformer202106117\Symplify\SmartFileSystem\Exception\FileNotFoundException(\sprintf('File "%s" not found in "%s".', $file, $location));
    }
    public function ensureDirectoryExists(string $directory, string $extraMessage = '') : void
    {
        if (\is_dir($directory) && \file_exists($directory)) {
            return;
        }
        $message = \sprintf('Directory "%s" was not found.', $directory);
        if ($extraMessage !== '') {
            $message .= ' ' . $extraMessage;
        }
        throw new \ConfigTransformer202106117\Symplify\SmartFileSystem\Exception\DirectoryNotFoundException($message);
    }
}
