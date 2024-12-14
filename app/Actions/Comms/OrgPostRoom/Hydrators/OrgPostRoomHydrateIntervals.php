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

class OrgPostRoomHydrateIntervals
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
        $metrics = ['dispatched', 'opened', 'clicked', 'unsubscribed', 'bounced'];
        $timeFrames = [
            'emails_all','emails_1y', 'emails_1q', 'emails_1m', 'emails_1w',
            'emails_3d', 'emails_1d', 'emails_ytd', 'emails_qtd', 'emails_mtd',
            'emails_wtd', 'emails_tdy', 'emails_lm', 'emails_lw', 'emails_ld'
        ];

        $timeFramesLastYear = array_filter(array_map(fn ($frame) => $frame !== 'emails_all' ? $frame . '_ly' : null, $timeFrames));


        $allKeys = [];
        foreach ($metrics as $metric) {
            foreach (array_merge($timeFrames, $timeFramesLastYear) as $frame) {
                $allKeys[] = "{$metric}_{$frame}";
            }
        }

        $allKeys = array_filter($allKeys);

        $stats = collect($allKeys)->mapWithKeys(function ($key) use ($orgPostRoom) {
            return [$key => $orgPostRoom->outboxes->sum(fn ($outbox) => $outbox->intervals->$key)];
        })->toArray();


        $orgPostRoom->intervals()->update($stats);

    }


}
