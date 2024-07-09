<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 11:34:12 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Timesheet;

use App\Actions\HumanResources\Employee\Hydrators\EmployeeHydrateTimesheets;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Guest\Hydrators\GuestHydrateTimesheets;
use App\Models\HumanResources\Timesheet;
use App\Models\SysAdmin\Guest;
use App\Models\HumanResources\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

class StoreTimesheet extends OrgAction
{
    public function handle(Employee|Guest $parent, array $modelData): Timesheet
    {
        data_set($modelData, 'group_id', $parent->group_id);
        data_set($modelData, 'organisation_id', $parent->organisation_id);
        data_set($modelData, 'subject_name', $parent->contact_name);

        /** @var Timesheet $timesheet */
        $timesheet = $parent->timesheets()->create($modelData);

        if ($parent instanceof Employee) {
            EmployeeHydrateTimesheets::dispatch($parent)->delay(now()->addSeconds(10));
        } else {
            GuestHydrateTimesheets::dispatch($parent)->delay(now()->addSeconds(10));
        }
        $timesheet->refresh();

        return $timesheet;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.edit");
    }


    public function rules(): array
    {
        return [
            'date'      => ['required', 'date'],
            'source_id' => ['sometimes', 'string', 'max:64'],
        ];
    }


    public function action(Employee|Guest $parent, $modelData): Timesheet
    {
        $this->asAction = true;
        $this->initialisation($parent->organisation, $modelData);

        return $this->handle($parent, $this->validatedData);
    }

    public function asController(Employee|Guest $parent, ActionRequest $request): Timesheet
    {
        return $this->handle($parent->organisation, $this->validatedData);
    }

    public function htmlResponse(Timesheet $timesheet): RedirectResponse
    {
        return Redirect::route('grp.org.hr.employees.show', $timesheet->id);
    }
}
