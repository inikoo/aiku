<?php

use App\Actions\Web\Webpage\BreakWebpageCache;

test('spreads its retries so a few seconds without varnish ports does not fail the job', function () {
    $job = BreakWebpageCache::makeJob(null);

    expect($job->tries)->toBe(5)
        ->and($job->backoff())->toBe([5, 15, 30, 60]);
});
