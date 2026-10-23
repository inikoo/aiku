<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 20:00:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Production\ManufactureTaskSession;

use App\Enums\Production\Artisan\ArtisanPayTypeEnum;
use App\Enums\Production\ManufactureTaskSession\ManufactureTaskSessionStateEnum;
use App\Enums\Production\ManufactureTaskSession\ManufactureTaskSessionTimeEditReasonEnum;
use App\Models\Production\ManufactureBreak;
use App\Models\Production\ManufactureTaskSession;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Nightwatch\Facades\Nightwatch;
use Lorisleiva\Actions\Concerns\AsAction;

class StopManufactureTaskSessionsAfterShift
{
    use AsAction;

    public string $commandSignature = 'production:stop_sessions_after_shift';
    public string $commandDescription = 'Close preparation and cleaning left running past the end of the shift, and flag production sessions and breaks left open';

    public const int GRACE_MINUTES = 30;

    /**
     * Preparation and cleaning have no count, so they are closed at the end of the shift. A production
     * session needs the quantity from the operator, so it is only flagged: the tablet and the Performance
     * page say it was left open, and a supervisor sets its real times once it is closed.
     *
     * @return array{closed: int, flagged: int, breaks_flagged: int}
     */
    public function handle(): array
    {
        $result = ['closed' => 0, 'flagged' => 0, 'breaks_flagged' => $this->flagBreaksLeftOpen()];

        $sessions = ManufactureTaskSession::where('state', ManufactureTaskSessionStateEnum::OPEN)
            ->whereNull('left_open_after_shift_at')
            ->whereNotNull('employee_id')
            ->where('started_at', '<', now()->subMinutes(self::GRACE_MINUTES))
            ->with('employee')
            ->get();

        foreach ($sessions as $session) {
            $window = $session->employee->shiftWindowOn($session->started_at);
            if (!$window) {
                continue;
            }

            $shiftEnd = $window[1]->copy()->setTimezone(config('app.timezone'));
            if ($session->started_at->greaterThanOrEqualTo($shiftEnd) || now()->lessThan($shiftEnd->copy()->addMinutes(self::GRACE_MINUTES))) {
                continue;
            }

            if ($session->isNonProductive()) {
                $this->closeAtShiftEnd($session, $shiftEnd);
                $result['closed']++;
            } else {
                $session->update(['left_open_after_shift_at' => $shiftEnd]);
                $result['flagged']++;
            }
        }

        return $result;
    }

    /**
     * A break started on the tablet only ends when the artisan presses Finish break or clocks in: one still open after
     * the shift is flagged for a supervisor to set when it really ended.
     */
    private function flagBreaksLeftOpen(): int
    {
        $flagged = 0;

        $breaks = ManufactureBreak::open()
            ->whereNotNull('planned_minutes')
            ->whereNull('left_open_after_shift_at')
            ->whereNotNull('employee_id')
            ->where('started_at', '<', now()->subMinutes(self::GRACE_MINUTES))
            ->with('employee')
            ->get();

        foreach ($breaks as $break) {
            $window = $break->employee->shiftWindowOn($break->started_at);
            if (!$window) {
                continue;
            }

            $shiftEnd = $window[1]->copy()->setTimezone(config('app.timezone'));
            if ($break->started_at->greaterThanOrEqualTo($shiftEnd) || now()->lessThan($shiftEnd->copy()->addMinutes(self::GRACE_MINUTES))) {
                continue;
            }

            $break->update(['left_open_after_shift_at' => $shiftEnd]);
            $flagged++;
        }

        return $flagged;
    }

    private function closeAtShiftEnd(ManufactureTaskSession $session, Carbon $shiftEnd): void
    {
        DB::transaction(function () use ($session, $shiftEnd) {
            $session = ManufactureTaskSession::lockForUpdate()->find($session->id);
            if ($session->state != ManufactureTaskSessionStateEnum::OPEN) {
                return;
            }

            $session->fill([
                'state'                    => ManufactureTaskSessionStateEnum::CLOSED,
                'ended_at'                 => $shiftEnd,
                'artisan_pay_type'         => $session->employee?->artisan_pay_type ?? ArtisanPayTypeEnum::PIECE_RATE,
                'left_open_after_shift_at' => $shiftEnd,
                'original_started_at'      => $session->started_at,
                'time_edit_reason'         => ManufactureTaskSessionTimeEditReasonEnum::CLOSED_AT_SHIFT_END,
                'time_edited_at'           => now(),
            ]);

            EditManufactureTaskSessionTimes::make()->recalculate($session);
        });
    }

    public function asCommand(Command $command): int
    {
        Nightwatch::dontSample();

        $result = $this->handle();
        $command->info("Closed {$result['closed']}, flagged {$result['flagged']}, breaks flagged {$result['breaks_flagged']}");

        return 0;
    }
}
