<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 12-12-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Comms\OrgPostRoom\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\Comms\OrgPostRoom;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OrgPostRoomHydrateMailshots
{
    use AsAction;
    use WithEnumStats;

    private OrgPostRoom $orgPostRoom;

    public function __construct(OrgPostRoom $orgPostRoom)
    {
        $this->orgPostRoom = $orgPostRoom;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->orgPostRoom->id))->dontRelease()];
    }

    public function handle(OrgPostRoom $orgPostRoom): void
    {

        $count = $orgPostRoom->outboxes()->with('mailshots')->get()->sum(function ($outbox) {
            return $outbox->mailshots->count();
        });

        $stats = [
            'number_mailshots' => $count,
        ];

        $orgPostRoom->stats()->update($stats);
    }

}
