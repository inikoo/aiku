<?php

namespace App\Actions\HumanResources\Overtime;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithHumanResourcesEditAuthorisation;
use App\Enums\HumanResources\Overtime\OvertimeRequestStatusEnum;
use App\Models\HumanResources\OvertimeRequest;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;
use Illuminate\Support\Facades\Auth;

class ApproveOvertimeRequest extends OrgAction
{
    use WithHumanResourcesEditAuthorisation;

    public function handle(OvertimeRequest $overtimeRequest): OvertimeRequest
    {
        if ($overtimeRequest->status !== OvertimeRequestStatusEnum::PENDING) {
            throw ValidationException::withMessages([
                'status' => __('Only pending overtime requests can be approved.'),
            ]);
        }

        $approverEmployeeId = Auth::user()->employee->id ?? null;

        $updateData = [
            'status'                  => OvertimeRequestStatusEnum::APPROVED,
            'approved_at'             => now(),
            'approved_by_employee_id' => $approverEmployeeId,
        ];

        if (
            is_null($overtimeRequest->recorded_start_at)
            && is_null($overtimeRequest->recorded_end_at)
            && is_null($overtimeRequest->recorded_duration_minutes)
        ) {
            $updateData['recorded_start_at'] = $overtimeRequest->requested_start_at;
            $updateData['recorded_end_at'] = $overtimeRequest->requested_end_at;
            $updateData['recorded_duration_minutes'] = $overtimeRequest->requested_duration_minutes;
            $updateData['recorded_by_employee_id'] = $approverEmployeeId;
        }

        $overtimeRequest->update($updateData);

        return $overtimeRequest->refresh();
    }

    public function action(OvertimeRequest $overtimeRequest): OvertimeRequest
    {
        return $this->handle($overtimeRequest);
    }

    public function asController(Organisation $organisation, OvertimeRequest $overtimeRequest, ActionRequest $request): OvertimeRequest
    {
        abort_unless($overtimeRequest->organisation_id === $organisation->id, 404);

        $this->initialisation($organisation, $request);

        return $this->handle($overtimeRequest);
    }

    public function htmlResponse(OvertimeRequest $overtimeRequest): RedirectResponse
    {
        request()->session()->flash('notification', [
            'status'      => 'success',
            'title'       => __('Success!'),
            'description' => __('Overtime request successfully approved.'),
        ]);

        return Redirect::back();
    }
}
