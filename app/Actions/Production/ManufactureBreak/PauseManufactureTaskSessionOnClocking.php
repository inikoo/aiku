<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 13:00:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Production\ManufactureBreak;

use App\Actions\Production\ManufactureTaskSession\CalculateManufactureTaskSessionBreakMinutes;
use App\Enums\Production\Artisan\ArtisanPayTypeEnum;
use App\Enums\Production\ManufactureTaskSession\ManufactureTaskSessionStateEnum;
use App\Models\HumanResources\Clocking;
use App\Models\HumanResources\Employee;
use App\Models\HumanResources\TimeTracker;
use App\Models\Production\ManufactureBreak;
use App\Models\Production\ManufactureTaskSession;
use Lorisleiva\Actions\Concerns\AsAction;

class PauseManufactureTaskSessionOnClocking
{
    use AsAction;

    /**
     * Hourly artisans take their breaks by clocking out at the kiosk: the clock-out opens a break with no planned
     * length, which pauses their open job session, and the next clock-in ends it.
     */
    public function handle(Employee $employee, Clocking $clocking): ?ManufactureBreak
    {
        if ($employee->artisan_pay_type !== ArtisanPayTypeEnum::HOURLY || !$user = $employee->getUser()) {
            return null;
        }

        $openBreak = ManufactureBreak::where('user_id', $user->id)->open()->first();
        $isClockOut = TimeTracker::where('end_clocking_id', $clocking->id)->exists();

        if (!$isClockOut) {
            if (!$openBreak || $openBreak->planned_minutes !== null) {
                return null;
            }

            $openBreak->update([
                'ended_at'             => $clocking->clocked_at,
                'minutes'              => (int) round($openBreak->started_at->diffInSeconds($clocking->clocked_at) / 60),
                'clock_in_clocking_id' => $clocking->id,
            ]);
            $this->recalculateOpenSession($user->id, $clocking);

            return $openBreak;
        }

        $openSession = $this->openSession($user->id);
        if ($openBreak || !$openSession) {
            return null;
        }

        return ManufactureBreak::create([
            'group_id'              => $openSession->group_id,
            'organisation_id'       => $openSession->organisation_id,
            'production_id'         => $openSession->production_id,
            'user_id'               => $user->id,
            'employee_id'           => $employee->id,
            'planned_minutes'       => null,
            'started_at'            => $clocking->clocked_at,
            'clock_out_clocking_id' => $clocking->id,
        ]);
    }

    private function openSession(int $userId): ?ManufactureTaskSession
    {
        return ManufactureTaskSession::where('user_id', $userId)
            ->where('state', ManufactureTaskSessionStateEnum::OPEN)
            ->first();
    }

    private function recalculateOpenSession(int $userId, Clocking $clocking): void
    {
        if ($openSession = $this->openSession($userId)) {
            CalculateManufactureTaskSessionBreakMinutes::run($openSession, $clocking->clocked_at);
        }
    }
}
