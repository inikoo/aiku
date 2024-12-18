<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 13-12-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Comms\OrgPostRoom\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\Comms\OrgPostRoom;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class OrgPostRoomHydrateRuns
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

        $count = DB::table('outbox_intervals')->leftjoin('outboxes', 'outbox_intervals.outbox_id', '=', 'outboxes.id')
            ->where('org_post_room_id', $orgPostRoom->id)->sum('runs_all');

        $orgPostRoom->intervals()->update(
            [
                'runs_all' => $count,
            ]
        );
    }

}
