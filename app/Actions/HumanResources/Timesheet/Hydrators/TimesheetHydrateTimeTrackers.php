<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Apr 2024 12:33:44 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Timesheet\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\HumanResources\Timesheet;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class TimesheetHydrateTimeTrackers
{
    use AsAction;
    use WithEnumStats;

    private Timesheet $timesheet;

    public function __construct(Timesheet $timesheet)
    {
        $this->timesheet = $timesheet;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->timesheet->id))->dontRelease()];
    }

    public function handle(Timesheet $timesheet): void
    {
        $stats = [
            'number_time_trackers' => $timesheet->timeTrackers()->count(),
        ];


        $timesheet->update($stats);
    }


}
