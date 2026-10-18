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

class RejectOvertimeRequest extends OrgAction
{
    use WithHumanResourcesEditAuthorisation;

    public function handle(OvertimeRequest $overtimeRequest): OvertimeRequest
    {
        if ($overtimeRequest->status !== OvertimeRequestStatusEnum::PENDING) {
            throw ValidationException::withMessages([
                'status' => __('Only pending overtime requests can be rejected.'),
            ]);
        }

        $overtimeRequest->update([
            'status'      => OvertimeRequestStatusEnum::REJECTED,
            'rejected_at' => now(),
        ]);

        return $overtimeRequest;
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
            'description' => __('Overtime request successfully rejected.'),
        ]);

        return Redirect::back();
    }
}
