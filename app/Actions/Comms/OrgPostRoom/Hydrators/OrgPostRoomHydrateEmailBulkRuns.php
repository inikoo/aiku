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
use Lorisleiva\Actions\Concerns\AsAction;

class OrgPostRoomHydrateEmailBulkRuns
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
        $stats = [
            'number_email_bulk_runs' => $orgPostRoom->outboxes()->with('emailBulkRuns')->get()->sum(function ($outbox) {
                return $outbox->emailBulkRuns->count();
            }),
        ];

        $orgPostRoom->stats()->update($stats);
    }

}
