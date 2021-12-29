<?php

namespace Devengine\LogKeeper\Repositories;

use League\Flysystem\FilesystemInterface;
use Traversable;

class LocalLogFileRepository implements LogFileRepository
{
    public function __construct(protected FilesystemInterface $filesystem)
    {
    }

    public function getIterator(): Traversable
    {
        $files = $this->filesystem->listContents(recursive: true);

        $testFile = static function (array $file): bool {

            if ('file' !== $file['type']) {
                return false;
            }

            if (!preg_match("#.+-\d{4}-\d{2}-\d{2}.log#", $file['path'])) {
                return false;
            }

            return true;

        };

        foreach ($files as $file) {

            if ($testFile($file)) {
                yield $file['path'];
            }

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
        return $this->filesystem->has($name);
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

        $tmp = tmpfile();

        $path = stream_get_meta_data($tmp)['uri'];

        $stream = \gzopen($path, 'wb9');

        \gzwrite($stream, $this->get($name));

        \gzclose($stream);

        $this->filesystem->writeStream($compressedFilename, $tmp);

        return true;
    }

    private function basename(string $name): string
    {
        $extension = pathinfo($name, PATHINFO_EXTENSION);

        return basename(pathinfo($name, PATHINFO_BASENAME), $extension);
    }
}