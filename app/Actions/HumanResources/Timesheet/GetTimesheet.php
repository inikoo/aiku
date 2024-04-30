<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Apr 2024 09:33:49 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Timesheet;

use App\Models\HumanResources\Employee;
use App\Models\SysAdmin\Guest;
use Carbon\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class GetTimesheet
{
    use AsAction;


    public function handle(Employee|Guest $subject, Carbon $date)
    {

        $timesheet = $subject->timesheets()->where('date', $date)->first();
        if(!$timesheet) {
            $timesheet=StoreTimesheet::make()->action(
                $subject,
                [
                'date' => $date,
            ]
            );
        }
        return $timesheet;

    }
}
