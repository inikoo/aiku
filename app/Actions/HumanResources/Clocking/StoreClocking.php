<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:46:32 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\HumanResources\Clocking;

use App\Actions\HumanResources\Clocking\Traits\SetClockingPhotoFromImage;
use App\Actions\HumanResources\Employee\Hydrators\EmployeeHydrateClockings;
use App\Actions\HumanResources\Timesheet\GetTimesheet;
use App\Actions\HumanResources\Timesheet\Hydrators\TimesheetHydrateTimeTrackers;
use App\Actions\HumanResources\TimeTracker\AddClockingToTimeTracker;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Guest\Hydrators\GuestHydrateClockings;
use App\Actions\Traits\WithBase64FileConverter;
use App\Actions\Traits\WithUpdateModelImage;
use App\Enums\HumanResources\Clocking\ClockingTypeEnum;
use App\Http\Resources\HumanResources\ClockingResource;
use App\Models\HumanResources\Clocking;
use App\Models\HumanResources\ClockingMachine;
use App\Models\HumanResources\Employee;
use App\Models\HumanResources\Workplace;
use App\Models\SysAdmin\Guest;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

class StoreClocking extends OrgAction
{
    use WithBase64FileConverter;
    use WithUpdateModelImage;

    public function handle(Organisation|User|Employee|Guest $generator, ClockingMachine|Workplace $parent, Employee|Guest $subject, array $modelData, ?UploadedFile $photo): Clocking
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

        $clocking->refresh();

        if ($photo) {
            SetClockingPhotoFromImage::run(
                clocking: $clocking,
                imagePath: $photo->getPathName(),
                originalFilename: $photo->getClientOriginalName(),
                extension: $photo->getClientOriginalExtension()
            );
        }

        TimesheetHydrateTimeTrackers::run($timesheet);

        if ($subject instanceof Employee) {
            EmployeeHydrateClockings::dispatch($subject);
        } else {
            GuestHydrateClockings::dispatch($subject);
        }

        return $clocking;
    }

    public function jsonResponse(Clocking $clocking): ClockingResource
    {
        return ClockingResource::make($clocking);
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
            'photo'      => [
                'sometimes',
                'nullable'
            ],
        ];
    }

    public function asController(ClockingMachine|Workplace $parent, Employee|Guest $subject, ActionRequest $request): Clocking
    {
        $this->initialisation($parent->organisation, $request);

        return $this->handle($request->user(), $parent, $subject, $this->validatedData, null);
    }

    public function inApi(ClockingMachine $clockingMachine, Employee $employee, ActionRequest $request): Clocking
    {
        $this->asAction = true;
        $this->fillFromRequest($request);
        $validated = $this->validateAttributes();

        return $this->handle($employee, $clockingMachine, $employee, Arr::except($validated, 'photo'), Arr::get($validated, 'photo'));
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

        if($this->has('photo')) {
            $this->set('photo', $this->convertBase64ToFile($this->get('photo')));
        }
    }

    public function action(Organisation|User|Employee|Guest $generator, ClockingMachine|Workplace $parent, Employee|Guest $subject, array $modelData): Clocking
    {
        $this->asAction = true;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($generator, $parent, $subject, $validatedData, null);
    }
}
