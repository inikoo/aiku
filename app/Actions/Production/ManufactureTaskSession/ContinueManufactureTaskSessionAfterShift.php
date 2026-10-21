<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Oct 2026 09:40:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Production\ManufactureTaskSession;

use App\Actions\OrgAction;
use App\Enums\Production\ManufactureTaskSession\ManufactureTaskSessionStateEnum;
use App\Models\Production\ManufactureTaskSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;

class ContinueManufactureTaskSessionAfterShift extends OrgAction
{
    /**
     * The operator did not finish before the shift ended and carries on. A clock-out over the end of the shift
     * already kept the night out of the session; without one the night counts, so the supervisor still sets the times.
     */
    public function handle(ManufactureTaskSession $session): ManufactureTaskSession
    {
        if ($session->state != ManufactureTaskSessionStateEnum::OPEN || !$session->left_open_after_shift_at) {
            throw ValidationException::withMessages(['state' => __('This job is not waiting for an answer about the end of the shift')]);
        }

        $session->update([
            'shift_end_answer'      => 'continued',
            'shift_end_resolved_at' => $session->shiftEndPause() ? now() : null,
        ]);

        return $session;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->id == $this->manufactureTaskSession->user_id;
    }

    private ManufactureTaskSession $manufactureTaskSession;

    public function action(ManufactureTaskSession $session): ManufactureTaskSession
    {
        $this->asAction               = true;
        $this->manufactureTaskSession = $session;
        $this->initialisationFromProduction($session->production, []);

        return $this->handle($session);
    }

    public function asController(ManufactureTaskSession $manufactureTaskSession, ActionRequest $request): ManufactureTaskSession
    {
        $this->manufactureTaskSession = $manufactureTaskSession;
        $this->initialisationFromProduction($manufactureTaskSession->production, $request);

        return $this->handle($manufactureTaskSession);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::back();
    }
}
