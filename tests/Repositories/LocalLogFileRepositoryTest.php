<?php

use Devengine\LogKeeper\Repositories\LocalLogFileRepository;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

test('it iterates log files', function () {

    $filesystem = new Filesystem(
        new Local(LOG_DIRECTORY)
    );

    $repository = new LocalLogFileRepository($filesystem);

    $iterator = $repository->getIterator();

    expect($iterator)->toBeInstanceOf(Generator::class);

    foreach ($iterator as $fileName) {
        expect($fileName)->toBeString();

        expect($fileName)->toMatch('#.+\-\d{4}\-\d{2}-\d{2}#');
    }
});