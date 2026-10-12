<?php

namespace App\Actions\HumanResources\AttendanceAdjustment;

use App\Actions\SysAdmin\User\GetUserCurrentEmployee;
use App\Actions\OrgAction;
use App\Enums\HumanResources\Attendance\AttendanceAdjustmentStatusEnum;
use App\Enums\HumanResources\Leave\LeaveStatusEnum;
use App\Http\Resources\HumanResources\AttendanceAdjustmentResource;
use App\Models\HumanResources\AttendanceAdjustment;
use App\Models\HumanResources\Employee;
use App\Models\HumanResources\Leave;
use App\Models\HumanResources\Timesheet;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;
use Illuminate\Contracts\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

class StoreAttendanceAdjustment extends OrgAction
{
    private ?Employee $employee = null;

    private function resolveEmployee(Request $request): ?Employee
    {
        return GetUserCurrentEmployee::fromRequest($request);
    }

    public function handle(Employee $employee, array $modelData): AttendanceAdjustment
    {
        $timezone  = $employee->organisation->timezone->name ?? 'UTC';
        $timesheet = isset($modelData['timesheet_id']) ? Timesheet::find($modelData['timesheet_id']) : null;
        $date      = $timesheet ? $timesheet->date->copy() : Carbon::parse($modelData['date']);

        $adjustment = AttendanceAdjustment::create([
            'group_id'            => $employee->group_id,
            'organisation_id'     => $employee->organisation_id,
            'employee_id'         => $employee->id,
            'employee_name'       => $employee->contact_name,
            'timesheet_id'        => $timesheet?->id,
            'date'                => $date->toDateString(),
            'original_start_at'   => $timesheet?->start_at,
            'original_end_at'     => $timesheet?->end_at,
            'requested_start_at'  => $this->momentOnDate($date, $modelData['requested_start_at'] ?? null, $timezone),
            'requested_end_at'    => $this->momentOnDate($date, $modelData['requested_end_at'] ?? null, $timezone),
            'reason'              => $modelData['reason'],
            'status'              => AttendanceAdjustmentStatusEnum::PENDING,
        ]);

        if (isset($modelData['attachments'])) {
            foreach ($modelData['attachments'] as $file) {
                $adjustment->addMedia($file)
                    ->withProperties([
                        'group_id' => $adjustment->group_id,
                        'type'     => 'attachment',
                        'ulid'     => (string) Str::ulid(),
                    ])
                    ->toMediaCollection('attachments');
            }
        }

        return $adjustment;
    }

    private function momentOnDate(Carbon $date, ?string $time, string $timezone): ?Carbon
    {
        if (!$time) {
            return null;
        }

        return Carbon::parse($date->toDateString().' '.$time, $timezone)->utc();
    }

    public function rules(): array
    {
        $this->employee ??= $this->resolveEmployee(request());

        return [
            'organisation'        => ['nullable', 'string'],
            'timesheet_id'        => ['nullable', 'integer', 'exists:timesheets,id'],
            'date'                => ['required', 'date'],
            'requested_start_at'  => ['nullable', 'required_without:requested_end_at', 'date_format:H:i'],
            'requested_end_at'    => ['nullable', 'required_without:requested_start_at', 'date_format:H:i'],
            'reason'              => ['required', 'string', 'max:1000'],
            'attachments'         => ['nullable', 'array', 'max:3'],
            'attachments.*'       => ['nullable', File::types(['pdf', 'jpg', 'jpeg', 'png'])->max(5 * 1024)],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        $startAt = $this->get('requested_start_at');
        $endAt   = $this->get('requested_end_at');
        if ($startAt && $endAt && $endAt <= $startAt) {
            $validator->errors()->add('requested_end_at', __('The check-out time must be after the check-in time.'));
        }

        if ($this->asAction || !$this->employee || !$this->get('date')) {
            return;
        }

        $timezone  = $this->employee->organisation->timezone->name ?? 'UTC';
        $timesheet = $this->get('timesheet_id') ? Timesheet::find($this->get('timesheet_id')) : null;

        if ($timesheet && ($timesheet->subject_type !== 'Employee' || $timesheet->subject_id !== $this->employee->id)) {
            $validator->errors()->add('date', __('You can only request a correction on your own timesheet.'));

            return;
        }

        $date  = ($timesheet ? $timesheet->date : Carbon::parse($this->get('date')))->toDateString();
        $today = now($timezone);

        if ($date > $today->toDateString()) {
            $validator->errors()->add('date', __('The date cannot be in the future.'));
        }

        if ($date < $today->copy()->subMonthNoOverflow()->toDateString()) {
            $validator->errors()->add('date', __('Requests can only go back one month.'));
        }

        if (!$timesheet && $this->employee->timesheets()->whereDate('date', $date)->exists()) {
            $validator->errors()->add('date', __('You already have clockings on this date. Request the correction from the Timesheets tab.'));
        }

        $existingLeave = Leave::where('employee_id', $this->employee->id)
            ->where('status', LeaveStatusEnum::APPROVED->value)
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->exists();

        if ($existingLeave) {
            $validator->errors()->add('date', __('You have approved leave on this date. Adjustments are not allowed.'));
        }

        $existingAdjustment = AttendanceAdjustment::where('employee_id', $this->employee->id)
            ->whereDate('date', $date)
            ->where('status', AttendanceAdjustmentStatusEnum::PENDING->value)
            ->exists();

        if ($existingAdjustment) {
            $validator->errors()->add('date', __('You already have a pending adjustment request for this date.'));
        }
    }

    public function asController(ActionRequest $request): AttendanceAdjustment
    {
        $this->employee = $this->resolveEmployee($request);

        if (!$this->employee) {
            abort(404, __('Employee record not found for current user.'));
        }

        $this->initialisation($this->employee->organisation, $request);

        return $this->handle($this->employee, $this->validatedData);
    }

    public function action(Employee $employee, array $modelData): AttendanceAdjustment
    {
        $this->asAction = true;
        $this->employee = $employee;
        $this->initialisation($employee->organisation, $modelData);

        return $this->handle($employee, $this->validatedData);
    }

    public function htmlResponse(AttendanceAdjustment $adjustment, ActionRequest $request): RedirectResponse
    {
        return Redirect::back()
            ->with('notification', [
                'status'      => 'success',
                'title'       => __('Success!'),
                'description' => __('Request submitted. It will apply once it is approved.'),
            ]);
    }

    public function jsonResponse(AttendanceAdjustment $adjustment): AttendanceAdjustmentResource
    {
        return AttendanceAdjustmentResource::make($adjustment);
    }
}
