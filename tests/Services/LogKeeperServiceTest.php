<?php

use Carbon\Carbon;
use Devengine\LogKeeper\Repositories\LocalLogFileRepository;
use Devengine\LogKeeper\Services\LogKeeperService;
use Devengine\LogKeeper\Support\LogUtil;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Symfony\Component\Finder\Finder;

test('it archives obsolete log files', function () {

    $config = [
        'days' => 30,
    ];

    $filesystem = new Filesystem(
        new LocalFilesystemAdapter(LOG_DIRECTORY)
    );

    $repository = new LocalLogFileRepository($filesystem);

    $service = new LogKeeperService(
        config: $config,
        repository: $repository,
    );

    $service->work();
    
    $files = Finder::create()
        ->in(LOG_DIRECTORY)
        ->name(['*.gz']);

    expect($files->hasResults())->toBeTrue();
    
    foreach ($files as $file) {
        $filename = $file->getRelativePathname();

        $date = LogUtil::parseDate($filename);

        expect(Carbon::now()->diffInDays($date))->toBeGreaterThan($config['days']);

        $correspondingLogFilename = $file->getBasename('.gz').'.log';

        expect($repository->exists($correspondingLogFilename))->toBeFalse();
    }

});