<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;

/**
 * The queue releases a unique lock when processing starts only if the queued object itself
 * is ShouldBeUniqueUntilProcessing. Actions are queued inside a decorator, so an action
 * declaring it is still unique until it finishes. An action that must take dispatches
 * arriving while it runs queues itself in this decorator instead.
 */
class BoundedUniqueUntilProcessingJobDecorator extends BoundedUniqueJobDecorator implements ShouldBeUniqueUntilProcessing
{
}
