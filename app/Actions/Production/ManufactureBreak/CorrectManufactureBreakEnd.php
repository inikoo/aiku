<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 23 Oct 2026 17:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Production\ManufactureBreak;

use App\Actions\OrgAction;
use App\Actions\Production\ManufactureTaskSession\CalculateManufactureTaskSessionBreakMinutes;
use App\Actions\Production\ManufactureTaskSession\EditManufactureTaskSessionTimes;
use App\Enums\Production\ManufactureTaskSession\ManufactureTaskSessionStateEnum;
use App\Models\Production\ManufactureBreak;
use App\Models\Production\ManufactureTaskSession;
use App\Models\SysAdmin\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;

/**
 * A supervisor sets when a break left open after the shift really ended. The time first recorded is kept, and the
 * job sessions the break overlaps get their hours and pay worked out again. Clockings are left to HR.
 */
class CorrectManufactureBreakEnd extends OrgAction
{
    private ?User $editor = null;
    private bool $canEditClosedPayWeek = false;

    public function handle(ManufactureBreak $break, array $modelData): ManufactureBreak
    {
        return DB::transaction(function () use ($break, $modelData) {
            $break   = ManufactureBreak::lockForUpdate()->find($break->id);
            $endedAt = Carbon::parse($modelData['ended_at'])->setTimezone(config('app.timezone'))->startOfMinute();

            if ($break->planned_minutes === null) {
                $this->refuse(__('A clock-out at the kiosk ends when the artisan clocks back in'));
            }
            if ($this->editor && $this->editor->id == $break->user_id) {
                $this->refuse(__('You cannot change your own break, ask a supervisor'));
            }
            if ($endedAt->lessThanOrEqualTo($break->started_at)) {
                $this->refuse(__('The end must be after the start of the break'));
            }
            if ($endedAt->greaterThan(now())) {
                $this->refuse(__('The end cannot be in the future'));
            }
            if (!$this->canEditClosedPayWeek && $break->started_at->lessThan(now()->startOfWeek())) {
                $this->refuse(__('The pay week of this break is closed, ask the system admin to change it'));
            }

            $previousEnd = $break->ended_at ?? now();
            $break->update([
                'ended_at'             => $endedAt,
                'minutes'              => min(ManufactureBreak::MAX_MINUTES, (int) round($break->started_at->diffInSeconds($endedAt) / 60)),
                'original_ended_at'    => $break->original_ended_at ?? $break->ended_at,
                'corrected_at'         => now(),
                'corrected_by_user_id' => $this->editor?->id,
            ]);

            ManufactureTaskSession::where('user_id', $break->user_id)
                ->whereIn('state', [ManufactureTaskSessionStateEnum::OPEN, ManufactureTaskSessionStateEnum::CLOSED])
                ->where('started_at', '<', $previousEnd->max($endedAt))
                ->where(fn ($query) => $query->whereNull('ended_at')->orWhere('ended_at', '>', $break->started_at))
                ->get()
                ->each(fn (ManufactureTaskSession $session) => $session->state == ManufactureTaskSessionStateEnum::OPEN
                    ? CalculateManufactureTaskSessionBreakMinutes::run($session, now())
                    : EditManufactureTaskSessionTimes::make()->recalculate($session));

            return $break;
        });
    }

    private function refuse(string $message): never
    {
        throw ValidationException::withMessages(['ended_at' => $message]);
    }

    public function rules(): array
    {
        return [
            'ended_at' => ['required', 'date'],
        ];
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

    public function action(ManufactureBreak $break, array $modelData, ?User $editor = null, bool $canEditClosedPayWeek = false): ManufactureBreak
    {
        $this->asAction             = true;
        $this->editor               = $editor;
        $this->canEditClosedPayWeek = $canEditClosedPayWeek;
        $this->initialisationFromProduction($break->production, $modelData);

        return $this->handle($break, $this->validatedData);
    }

    public function asController(ManufactureBreak $manufactureBreak, ActionRequest $request): ManufactureBreak
    {
        $this->editor = $request->user();
        $this->initialisationFromProduction($manufactureBreak->production, $request);

        return $this->handle($manufactureBreak, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::back();
    }
}
