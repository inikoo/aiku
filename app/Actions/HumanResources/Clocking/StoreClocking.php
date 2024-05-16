<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:46:32 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\HumanResources\Clocking;

use App\Actions\HumanResources\Employee\Hydrators\EmployeeHydrateClockings;
use App\Actions\HumanResources\Timesheet\GetTimesheet;
use App\Actions\HumanResources\Timesheet\Hydrators\TimesheetHydrateTimeTrackers;
use App\Actions\HumanResources\TimeTracker\AddClockingToTimeTracker;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Guest\Hydrators\GuestHydrateClockings;
use App\Enums\HumanResources\Clocking\ClockingTypeEnum;
use App\Models\HumanResources\Clocking;
use App\Models\HumanResources\ClockingMachine;
use App\Models\HumanResources\Employee;
use App\Models\HumanResources\Workplace;
use App\Models\SysAdmin\Guest;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;
use Google\Service\ShoppingContent\ActionReason;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

class StoreClocking extends OrgAction
{
    public function handle(Organisation|User|Employee|Guest $generator, ClockingMachine|Workplace $parent, Employee|Guest $subject, array $modelData): Clocking
    {
        data_set($modelData, 'generator_type', class_basename($generator));
        data_set($modelData, 'generator_id', $generator->id);

        if (class_basename($parent::class) == 'ClockingMachine') {
            $modelData['clocking_machine_id'] = $parent->id;
            $modelData['workplace_id']        = $parent->workplace_id;
            $modelData['type']                = ClockingTypeEnum::CLOCKING_MACHINE;
        } else {
            $modelData['workplace_id'] = $parent->id;
            $modelData['type']         = ClockingTypeEnum::MANUAL;
        }
        data_set($modelData, 'clocked_at', now(), overwrite: false);

        $timesheet = GetTimesheet::run($subject, $modelData['clocked_at']);
        data_set($modelData, 'timesheet_id', $timesheet->id);


        /** @var Clocking $clocking */
        $clocking = $subject->clockings()->create($modelData);
        AddClockingToTimeTracker::run($timesheet, $clocking);

        TimesheetHydrateTimeTrackers::run($timesheet);

        if ($subject instanceof Employee) {
            EmployeeHydrateClockings::dispatch($subject);
        } else {
            GuestHydrateClockings::dispatch($subject);
        }

        return $clocking;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("human-resources.workplaces.{$this->organisation->id}.edit");
    }

    public function rules(): array
    {
        return [
            'clocked_at' => ['sometimes', 'required', 'date'],
        ];
    }

    public function asController(ClockingMachine|Workplace $parent, Employee|Guest $subject, ActionRequest $request): Clocking
    {
        $this->initialisation($parent->organisation, $request);

        return $this->handle($request->user(), $parent, $subject, $this->validatedData);
    }

    public function inApi(ClockingMachine $clockingMachine, Employee $employee, ActionRequest $request): Clocking
    {
        return $this->handle($employee, $clockingMachine, $employee, $request->all());
    }


    public function htmlResponse(Clocking $clocking): RedirectResponse
    {
        if (!$clocking->clocking_machine_id) {
            return Redirect::route('grp.org.hr.workplaces.show.clockings.show', [
                $clocking->workplace->slug,
                $clocking->slug
            ]);
        } else {
            return Redirect::route('grp.org.hr.workplaces.show.clocking-machines.show.clockings.show', [
                $clocking->workplace->slug,
                $clocking->clockingMachine->slug,
                $clocking->slug
            ]);
        }
    }

    public function prepareForValidation(): void
    {
        if ($this->has('clocked_at') && is_string($this->get('clocked_at'))) {
            $this->set('clocked_at', Carbon::parse($this->get('clocked_at')));
        }
    }

    public function action(Organisation|User|Employee|Guest $generator, ClockingMachine|Workplace $parent, Employee|Guest $subject, array $modelData): Clocking
    {
        $this->asAction = true;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($generator, $parent, $subject, $validatedData);
    }
}
