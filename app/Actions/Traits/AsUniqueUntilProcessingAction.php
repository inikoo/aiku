<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Traits;

use App\Jobs\BoundedUniqueUntilProcessingJobDecorator;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Decorators\UniqueJobDecorator;

/**
 * AsAction for an action that implements ShouldBeUniqueUntilProcessing: queues it in a
 * decorator the queue recognises, so its lock is released when a run starts and a dispatch
 * arriving while it runs queues the next run instead of being dropped.
 */
trait AsUniqueUntilProcessingAction
{
    use AsAction;

    public static function makeUniqueJob(mixed ...$arguments): UniqueJobDecorator
    {
        return new BoundedUniqueUntilProcessingJobDecorator(static::class, ...$arguments);
    }
}
