<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Apr 2024 09:57:32 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Timesheet\UI;

use App\Models\HumanResources\Timesheet;
use Lorisleiva\Actions\Concerns\AsAction;

class GetTimesheetShowcase
{
    use AsAction;

    public function handle(Timesheet $timesheet): array
    {
        return [
            'work_start'      => $timesheet->start_at,
            'work_duration'   => $timesheet->working_duration,
            'breaks_duration' => $timesheet->breaks_duration,
            'work_end_at'     => $timesheet->end_at,
            'overtime'        => $timesheet->overtime,
            'about'           => $timesheet->about
        ];
    }
}
