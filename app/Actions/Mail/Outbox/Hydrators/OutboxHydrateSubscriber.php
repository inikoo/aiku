<?php
/*
 * author Arya Permana - Kirin
 * created on 05-11-2024-10h-39m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Mail\Outbox\Hydrators;

use App\Models\Mail\Outbox;
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
