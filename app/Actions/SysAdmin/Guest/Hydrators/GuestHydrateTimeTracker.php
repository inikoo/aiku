<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Apr 2024 12:33:44 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Guest\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\HumanResources\TimeTracker\TimeTrackerStatusEnum;
use App\Models\HumanResources\Timesheet;
use App\Models\SysAdmin\Guest;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class GuestHydrateTimeTracker
{
    use AsAction;
    use WithEnumStats;

    private Guest $guest;

    public function __construct(Guest $guest)
    {
        $this->guest = $guest;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->guest->id))->dontRelease()];
    }

    public function handle(Guest $guest): void
    {
        $stats = [
            'number_time_trackers' => $guest->timeTrackers()->count(),
        ];


        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'time_trackers',
                field: 'status',
                enum: TimeTrackerStatusEnum::class,
                models: Timesheet::class,
                where: function ($q) use ($guest) {
                    $q->where('subject_type', 'Guest')->where('subject_id', $guest->id);
                }
            )
        );


        $guest->stats()->update($stats);
    }


}
