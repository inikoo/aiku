<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Apr 2024 12:33:44 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\TimeTracker;

use App\Actions\HumanResources\Employee\Hydrators\EmployeeHydrateTimeTracker;
use App\Actions\HumanResources\Timesheet\Hydrators\TimesheetHydrateTimeTrackers;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Guest\Hydrators\GuestHydrateTimeTracker;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\HumanResources\TimeTracker\TimeTrackerStatusEnum;
use App\Models\HumanResources\Clocking;
use App\Models\HumanResources\TimeTracker;
use Lorisleiva\Actions\ActionRequest;

class CloseTimeTracker extends OrgAction
{
    use WithActionUpdate;

    public function handle(TimeTracker $timeTracker, Clocking $clocking, array $modelData): TimeTracker
    {


        data_set($modelData, 'status', TimeTrackerStatusEnum::CLOSED);
        data_set($modelData, 'ends_at', $clocking->clocked_at);
        data_set($modelData, 'end_clocking_id', $clocking->id);


        $timeTracker = $this->update($timeTracker, $modelData);


        if($timeTracker->subject_type === 'Employee') {
            EmployeeHydrateTimeTracker::dispatch($timeTracker->subject);
        } else {
            GuestHydrateTimeTracker::dispatch($timeTracker->subject);
        }

        TimesheetHydrateTimeTrackers::dispatch($timeTracker->timesheet);


        return $timeTracker;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return false;
    }


    public function rules(): array
    {
        return [

        ];
    }


    public function action(TimeTracker $timeTracker, Clocking $clocking, $modelData): TimeTracker
    {
        $this->asAction = true;
        $this->initialisation($timeTracker->timesheet->organisation, $modelData);

        return $this->handle($timeTracker, $clocking, $this->validatedData);
    }


}
