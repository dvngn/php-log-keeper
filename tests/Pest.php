<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

// uses(Tests\TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Symfony\Component\Finder\Finder;

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

const LOG_DIRECTORY = __DIR__.'/Data/logs';

uses()
    ->beforeEach(function () {

        if (!is_dir(LOG_DIRECTORY.'/sub')) {
            mkdir(LOG_DIRECTORY.'/sub');
        }

        $days = CarbonPeriod::create(Carbon::today()->subMonth()->startOfMonth()->startOfDay(), Carbon::today()->endOfMonth()->endOfDay())->days();

        foreach ($days as $day) {
            $minutes = CarbonPeriod::create($day->clone()->startOfDay(), $day->clone()->endOfDay())->setDateInterval(\Carbon\CarbonInterval::minutes(4));

            $data = [];

            foreach ($minutes as $minute) {
                $data[] = sprintf("[%s] production.INFO: testing", $minute->format('Y-m-d H:i:s'));
            }

            file_put_contents(LOG_DIRECTORY.'/test-'.$day->format('Y-m-d').'.log', implode("\n", $data));
            file_put_contents(LOG_DIRECTORY.'/sub/sub-'.$day->format('Y-m-d').'.log', implode("\n", $data));
        }
    })
    ->afterEach(function () {

        $files = Finder::create()
            ->in(LOG_DIRECTORY)
            ->ignoreUnreadableDirs()
            ->name(['*.log', '*.gz']);

        foreach ($files as $file) {
            unlink($file->getRealPath());
        }
    })
    ->in('Repositories', 'Services');
