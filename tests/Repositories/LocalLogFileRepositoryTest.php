<?php

use Devengine\LogKeeper\Repositories\LocalLogFileRepository;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

test('it iterates log files', function () {

    $filesystem = new Filesystem(
        new LocalFilesystemAdapter(LOG_DIRECTORY)
    );

    $repository = new LocalLogFileRepository($filesystem);

    $iterator = $repository->getIterator();

    expect($iterator)->toBeInstanceOf(Generator::class);

    foreach ($iterator as $fileName) {
        expect($fileName)->toBeString();

        expect($fileName)->toMatch('#.+\-\d{4}\-\d{2}-\d{2}#');
    }
});