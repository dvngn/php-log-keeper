<?php

namespace Devengine\LogKeeper\Repositories;

use League\Flysystem\FilesystemOperator;
use League\Flysystem\FilesystemReader;
use League\Flysystem\StorageAttributes;
use Traversable;

class LocalLogFileRepository implements LogFileRepository
{
    public function __construct(protected FilesystemOperator $filesystem)
    {
    }

    public function getIterator(): Traversable
    {
        $files = $this->filesystem->listContents(location: '', deep: FilesystemReader::LIST_DEEP)
            ->filter(static function (StorageAttributes $file): bool {
                return (bool)preg_match("#.+-\d{4}-\d{2}-\d{2}.log#", $file->path());
            });

        foreach ($files as $file) {
            /** @var StorageAttributes $file */
            yield $file->path();
        }
    }

    /**
     * @throws \League\Flysystem\FilesystemException
     */
    public function get(string $name): string
    {
        return $this->filesystem->read($name);
    }

    /**
     * @throws \League\Flysystem\FilesystemException
     */
    public function exists(string $name): bool
    {
        return $this->filesystem->fileExists($name);
    }

    /**
     * @throws \League\Flysystem\FilesystemException
     */
    public function put(string $name, string $content): bool
    {
        $this->filesystem->write($name, $content);

        return true;
    }

    /**
     * @throws \League\Flysystem\FilesystemException
     */
    public function delete(string $name): bool
    {
        $this->filesystem->delete($name);

        return true;
    }

    public function compress(string $name): bool
    {
        $basename = $this->basename($name);
        $directory = pathinfo($name, PATHINFO_DIRNAME);

        $compressedFilename = rtrim($directory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$basename."gz";

        $stream = tmpfile();

        \gzwrite($stream, $this->get($name));

        $this->filesystem->writeStream($compressedFilename, $stream);

        \gzclose($stream);

        return true;
    }

    private function basename(string $name): string
    {
        $extension = pathinfo($name, PATHINFO_EXTENSION);

        return basename(pathinfo($name, PATHINFO_BASENAME), $extension);
    }
}