<?php
/*
 * author Arya Permana - Kirin
 * created on 02-12-2024-16h-48m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Comms\PostRoom\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Comms\Outbox\OutboxStateEnum;
use App\Enums\Comms\Outbox\OutboxCodeEnum;
use App\Models\Comms\Outbox;
use App\Models\Comms\PostRoom;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class PostRoomHydrateIntervals
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
        $metrics = ['dispatched', 'opened', 'clicked', 'unsubscribed', 'bounced'];
        $timeFrames = [
            'emails_all', 'emails_1y', 'emails_1q', 'emails_1m', 'emails_1w', 
            'emails_3d', 'emails_1d', 'emails_ytd', 'emails_qtd', 'emails_mtd', 
            'emails_wtd', 'emails_tdy', 'emails_lm', 'emails_lw', 'emails_ld'
        ];
        $timeFramesLastYear = array_map(fn($frame) => $frame . '_ly', $timeFrames);
        
        $allKeys = [];
        foreach ($metrics as $metric) {
            foreach (array_merge($timeFrames, $timeFramesLastYear) as $frame) {
                $allKeys[] = "{$metric}_{$frame}";
            }
        }
        
        $stats = collect($allKeys)->mapWithKeys(function ($key) use ($postRoom) {
            return [$key => $postRoom->outboxes->sum(fn($outbox) => $outbox->intervals->$key)];
        })->toArray();


        $postRoom->stats()->update($stats);

    }


}
