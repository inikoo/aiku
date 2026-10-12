<?php

namespace App\Actions\HumanResources\AttendanceAdjustment;

use App\Actions\OrgAction;
use App\Actions\SysAdmin\User\GetUserCurrentEmployee;
use App\Enums\HumanResources\Attendance\AttendanceAdjustmentStatusEnum;
use App\Models\HumanResources\AttendanceAdjustment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;

class CancelAttendanceAdjustment extends OrgAction
{
    public function handle(AttendanceAdjustment $adjustment): void
    {
        $adjustment->delete();
    }

    public function asController(AttendanceAdjustment $adjustment, ActionRequest $request): RedirectResponse
    {
        $employee = GetUserCurrentEmployee::fromRequest($request);

        if (!$employee || $adjustment->employee_id !== $employee->id) {
            abort(403);
        }

        $this->initialisation($employee->organisation, $request);

        if ($adjustment->status !== AttendanceAdjustmentStatusEnum::PENDING) {
            throw ValidationException::withMessages([
                'status' => __('Only a pending request can be cancelled.'),
            ]);
        }

        $this->handle($adjustment);

        return Redirect::back()->with('notification', [
            'status'      => 'success',
            'title'       => __('Success!'),
            'description' => __('Request cancelled.'),
        ]);
    }
}
