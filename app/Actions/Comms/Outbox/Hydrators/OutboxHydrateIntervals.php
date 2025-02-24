<?php

/*
 * author Arya Permana - Kirin
 * created on 02-12-2024-13h-49m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Comms\Outbox\Hydrators;

use App\Actions\Traits\Hydrators\WithHydrateIntervals;
use App\Enums\Comms\DispatchedEmail\DispatchedEmailStateEnum;
use Illuminate\Support\Facades\DB;
use App\Models\Comms\Outbox;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OutboxHydrateIntervals
{
    use AsAction;
    use WithHydrateIntervals;

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
        $dateRanges = $this->getDateRanges();
        $results = [];

        foreach ($dateRanges as $rangeKey => $range) {
            $query = $outbox->dispatchedEmails();

            if ($range !== null) {
                $query->whereBetween('created_at', $range);
            }

            $totalDispatched = $query->count();

            $stateCounts = $query
                ->whereIn('state', [
                    DispatchedEmailStateEnum::OPENED,
                    DispatchedEmailStateEnum::CLICKED,
                    DispatchedEmailStateEnum::UNSUBSCRIBED,
                    DispatchedEmailStateEnum::SOFT_BOUNCE,
                    DispatchedEmailStateEnum::HARD_BOUNCE
                ])
                ->select(
                    DB::raw("
                        CASE 
                            WHEN state IN ('soft_bounce', 'hard_bounce') THEN 'bounced'
                            ELSE state 
                        END as state_group
                    "),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('state_group')
                ->pluck('count', 'state_group')
                ->toArray();

            foreach (['opened', 'clicked', 'unsubscribed', 'bounced'] as $state) {
                $stateCounts[$state] = $stateCounts[$state] ?? 0;
            }

            $results[$rangeKey] = [
                'total' => $totalDispatched,
                'states' => $stateCounts,
            ];
        }

        $updateData = [];
        foreach ($results as $rangeKey => $result) {
            $updateData["dispatched_emails_$rangeKey"] = $result['total'];
            foreach ($result['states'] as $state => $count) {
                $key = strtolower($state);
                $suffix = '_emails_';
                if ($key == 'unsubscribed') {
                    $suffix = '_';
                }
                $updateData["$key$suffix$rangeKey"] = $count;
            }
        }

        $outbox->intervals()->update($updateData);
    }


}
