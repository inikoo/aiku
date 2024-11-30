<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\Outbox\Hydrators;

use App\Models\Comms\Outbox;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OutboxHydrateSubscriber
{
    use AsAction;
    private Outbox $outbox;

    public function __construct(Outbox $outbox)
    {
        $this->outbox = $outbox;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->outbox->id))->dontRelease()];
    }


    public function handle(Outbox $outbox): void
    {
        $stats = [
            'number_subscribers' => $outbox->subscribers()->count(),
            'number_unsubscribed' => $outbox->unsubscribed()->count(),
        ];

        $outbox->stats()->update(
            $stats
        );
    }



}
