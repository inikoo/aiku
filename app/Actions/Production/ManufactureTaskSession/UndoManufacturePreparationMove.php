<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 23 Oct 2026 20:30:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Production\ManufactureTaskSession;

use App\Actions\OrgAction;
use App\Enums\Production\ManufactureTaskSession\ManufactureTaskSessionStateEnum;
use App\Models\Production\ManufactureTaskSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;

class UndoManufacturePreparationMove extends OrgAction
{
    private bool $canEditClosedPayWeek = false;

    /**
     * Short preparation added to the step started after it goes back to being Preparation, and the step starts when it first did.
     */
    public function handle(ManufactureTaskSession $preparation): ManufactureTaskSession
    {
        return DB::transaction(function () use ($preparation) {
            $preparation = ManufactureTaskSession::lockForUpdate()->find($preparation->id);
            $job         = $preparation->moved_into_session_id ? ManufactureTaskSession::lockForUpdate()->find($preparation->moved_into_session_id) : null;

            if (!$job || $preparation->state != ManufactureTaskSessionStateEnum::VOIDED) {
                throw ValidationException::withMessages([
                    'state' => __('This preparation was not added to a job step'),
                ]);
            }
            if (!$this->canEditClosedPayWeek && ($job->ended_at ?? now())->lessThan(now()->startOfWeek())) {
                throw ValidationException::withMessages([
                    'state' => __('The pay week of this session is closed, ask the system admin to change it'),
                ]);
            }
            if (!$job->started_at->equalTo($preparation->started_at)) {
                throw ValidationException::withMessages([
                    'state' => __('The times of the job step were changed since, edit its times instead'),
                ]);
            }

            $job->started_at = $preparation->started_at->copy()->addSeconds($preparation->moved_seconds);
            if ($job->state == ManufactureTaskSessionStateEnum::CLOSED) {
                EditManufactureTaskSessionTimes::make()->recalculate($job);
            } else {
                $job->save();
            }

            $preparation->update([
                'state'                 => ManufactureTaskSessionStateEnum::CLOSED,
                'moved_into_session_id' => null,
                'moved_seconds'         => null,
            ]);

            return $preparation;
        });
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        $this->canEditClosedPayWeek = $request->user()->authTo('sysadmin.edit');

        return $this->canEditClosedPayWeek || $request->user()->authTo([
            'org-supervisor.'.$this->organisation->id,
            "productions_operations.{$this->production->id}.orchestrate",
        ]);
    }

    public function action(ManufactureTaskSession $preparation, bool $canEditClosedPayWeek = false): ManufactureTaskSession
    {
        $this->asAction             = true;
        $this->canEditClosedPayWeek = $canEditClosedPayWeek;
        $this->initialisationFromProduction($preparation->production, []);

        return $this->handle($preparation);
    }

    public function asController(ManufactureTaskSession $manufactureTaskSession, ActionRequest $request): ManufactureTaskSession
    {
        $this->initialisationFromProduction($manufactureTaskSession->production, $request);

        return $this->handle($manufactureTaskSession);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::back();
    }
}
