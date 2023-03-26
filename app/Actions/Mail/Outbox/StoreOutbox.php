<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Outbox;

use App\Models\Mail\Mailroom;
use App\Models\Mail\Outbox;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreOutbox
{
    use AsAction;

    public function handle(Mailroom $mailroom, array $modelData): Outbox
    {
        /** @var Outbox $outbox */
        $outbox = $mailroom->outboxes()->create($modelData);
        $outbox->stats()->create();

        return $outbox;
    }
}
