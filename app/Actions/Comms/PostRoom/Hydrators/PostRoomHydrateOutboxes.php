<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\PostRoom\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Comms\Outbox\OutboxStateEnum;
use App\Enums\Comms\Outbox\OutboxCodeEnum;
use App\Models\Comms\Outbox;
use App\Models\Comms\PostRoom;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class PostRoomHydrateOutboxes
{
    use AsAction;
    use WithEnumStats;

    private PostRoom $postRoom;

    public function __construct(PostRoom $postRoom)
    {
        $this->postRoom = $postRoom;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->postRoom->id))->dontRelease()];
    }

    public function handle(PostRoom $postRoom): void
    {
        $stats = [
            'number_outboxes' => $postRoom->outboxes()->count(),
        ];

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'outboxes',
                field: 'type',
                enum: OutboxCodeEnum::class,
                models: Outbox::class,
                where: function ($q) use ($postRoom) {
                    $q->where('post_room_id', $postRoom->id);
                }
            )
        );


        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'outboxes',
                field: 'state',
                enum: OutboxStateEnum::class,
                models: Outbox::class,
                where: function ($q) use ($postRoom) {
                    $q->where('post_room_id', $postRoom->id);
                }
            )
        );

        $postRoom->stats()->update($stats);

    }


}
