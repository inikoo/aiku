<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 11 Jun 2024 22:14:16 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

 namespace App\Actions\Mail\PostRoom\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Mail\Outbox\OutboxStateEnum;
use App\Enums\Mail\Outbox\OutboxTypeEnum;
use App\Models\Mail\Outbox;
use App\Models\Mail\PostRoom;
use App\Models\SysAdmin\Organisation;
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
                enum: OutboxTypeEnum::class,
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
