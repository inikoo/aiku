<?php

/*
 * author Arya Permana - Kirin
 * created on 02-12-2024-13h-49m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Comms\Outbox\Hydrators;

use App\Enums\Comms\DispatchedEmail\DispatchedEmailStateEnum;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Comms\Outbox;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OutboxHydrateIntervals
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
        $now = Carbon::now();

        $dateRanges = [
            'all' => null,
            '1y' => [$now->subYear(), $now],
            '1q' => [$now->subMonths(3), $now],
            '1m' => [$now->subMonth(), $now],
            '1w' => [$now->subWeek(), $now],
            '3d' => [$now->subDays(3), $now],
            '1d' => [$now->subDay(), $now],
            'ytd' => [$now->copy()->startOfYear(), $now],
            'qtd' => [$now->copy()->startOfQuarter(), $now],
            'mtd' => [$now->copy()->startOfMonth(), $now],
            'wtd' => [$now->copy()->startOfWeek(), $now],
            'tdy' => [$now->copy()->startOfDay(), $now],
            'lm' => [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()],
            'lw' => [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()],
            'ld' => [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay()],

            'all_ly' => [$now->copy()->subYear()->startOfYear(), $now->copy()->subYear()->endOfYear()],
            '1y_ly' => [$now->copy()->subYears(2), $now->copy()->subYear()],
            '1q_ly' => [$now->copy()->subYear()->subMonths(3), $now->copy()->subYear()],
            '1m_ly' => [$now->copy()->subYear()->subMonth(), $now->copy()->subYear()],
            '1w_ly' => [$now->copy()->subYear()->subWeek(), $now->copy()->subYear()],
            '3d_ly' => [$now->copy()->subYear()->subDays(3), $now->copy()->subYear()],
            '1d_ly' => [$now->copy()->subYear()->subDay(), $now->copy()->subYear()],
            'ytd_ly' => [$now->copy()->subYear()->startOfYear(), $now->copy()->subYear()->endOfYear()],
            'qtd_ly' => [$now->copy()->subYear()->startOfQuarter(), $now->copy()->subYear()->endOfQuarter()],
            'mtd_ly' => [$now->copy()->subYear()->startOfMonth(), $now->copy()->subYear()->endOfMonth()],
            'wtd_ly' => [$now->copy()->subYear()->startOfWeek(), $now->copy()->subYear()->endOfWeek()],
            'tdy_ly' => [$now->copy()->subYear()->startOfDay(), $now->copy()->subYear()->endOfDay()],
            'lm_ly' => [$now->copy()->subYear()->subMonth()->startOfMonth(), $now->copy()->subYear()->subMonth()->endOfMonth()],
            'lw_ly' => [$now->copy()->subYear()->subWeek()->startOfWeek(), $now->copy()->subYear()->subWeek()->endOfWeek()],
            'ld_ly' => [$now->copy()->subYear()->subDay()->startOfDay(), $now->copy()->subYear()->subDay()->endOfDay()],
        ];

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
                            WHEN state IN ('soft-bounce', 'hard-bounce') THEN 'bounced'
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
            $updateData["dispatched_emails_{$rangeKey}"] = $result['total'];
            foreach ($result['states'] as $state => $count) {
                $key = strtolower($state);
                $updateData["{$key}_emails_{$rangeKey}"] = $count;
            }
        }

        $outbox->intervals()->update($updateData);
    }


}
