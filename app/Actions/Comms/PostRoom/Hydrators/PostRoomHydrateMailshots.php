<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 13-12-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Comms\PostRoom\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\Comms\PostRoom;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class PostRoomHydrateMailshots
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

        $count = $postRoom->outboxes()->with('mailshots')->get()->sum(function ($outbox) {
            return $outbox->mailshots->count();
        });

        $postRoom->intervals()->update(
            [
                'runs_all' => $count,
            ]
        );


    }

}
