<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Oct 2026 16:30:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Production\ManufactureArtisanDay;

use App\Enums\Production\ManufactureArtisanDay\ManufactureArtisanDayStatusEnum;
use App\Enums\Production\ManufactureTaskSession\ManufactureTaskSessionStateEnum;
use App\Models\HumanResources\Employee;
use App\Models\HumanResources\WorkSchedule;
use App\Models\Production\ManufactureArtisanDay;
use App\Models\Production\ManufactureBreak;
use App\Models\Production\ManufactureHoursTolerance;
use App\Models\Production\ManufactureTaskSession;
use App\Models\Production\Production;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class HydrateManufactureArtisanDays
{
    use AsAction;

    public const int MAX_DAYS = 92;

    /** @var array<int, WorkSchedule|null> */
    private array $schedules = [];

    /**
     * Works out every artisan day of the period again from the floor sessions. A day belongs to the shift it was
     * started in, so a night shift counts on the evening it began.
     */
    public function handle(Production $production, Carbon $from, Carbon $to): void
    {
        $from     = $from->copy()->max($to->copy()->subDays(self::MAX_DAYS));
        $fromDate = $from->toDateString();
        $toDate   = $to->toDateString();

        $sessions = ManufactureTaskSession::where('production_id', $production->id)
            ->whereNotNull('employee_id')
            ->whereIn('state', [ManufactureTaskSessionStateEnum::CLOSED, ManufactureTaskSessionStateEnum::OPEN])
            ->whereBetween('started_at', [$from->copy()->startOfDay()->subDay(), $to->copy()->endOfDay()->addDay()])
            ->with('employee.organisation.timezone')
            ->get();

        $breaksByUser = ManufactureBreak::whereIn('user_id', $sessions->pluck('user_id')->unique())
            ->whereBetween('started_at', [$from->copy()->startOfDay()->subDay(), $to->copy()->endOfDay()->addDay()])
            ->get(['user_id', 'started_at'])
            ->groupBy('user_id');

        $days = $sessions
            ->groupBy(fn (ManufactureTaskSession $session) => $session->employee_id.'|'.$this->shiftDay($session->employee, $session->started_at))
            ->filter(function (Collection $daySessions, string $key) use ($fromDate, $toDate) {
                $date = explode('|', $key)[1];

                return $date >= $fromDate && $date <= $toDate;
            });

        $existing = ManufactureArtisanDay::where('production_id', $production->id)
            ->whereBetween('date', [$fromDate, $toDate])
            ->get()
            ->keyBy(fn (ManufactureArtisanDay $day) => $day->employee_id.'|'.$day->date->toDateString());

        foreach ($existing as $key => $day) {
            if (!$days->has($key)) {
                $day->resolution ? $days->put($key, collect()) : $day->delete();
            }
        }

        $tolerances = [];
        foreach ($days as $key => $daySessions) {
            $date     = explode('|', $key)[1];
            $employee = $daySessions->first()?->employee ?? $existing[$key]->employee;
            $tolerances[$date] ??= ManufactureHoursTolerance::minutesOn($production->id, $date);

            $this->hydrateDay($production, $employee, $date, $daySessions, $tolerances[$date], $existing[$key] ?? null, $breaksByUser);
        }
    }

    private function schedule(Employee $employee): ?WorkSchedule
    {
        if (!array_key_exists($employee->id, $this->schedules)) {
            $this->schedules[$employee->id] = $employee->getEffectiveWorkSchedule()?->load('days', 'timezone');
        }

        return $this->schedules[$employee->id];
    }

    private function shiftDay(Employee $employee, Carbon $startedAt): string
    {
        $schedule = $this->schedule($employee);
        $window   = $schedule ? $employee->shiftWindowOn($startedAt, $schedule) : null;
        if ($window) {
            return $window[0]->toDateString();
        }

        return $startedAt->copy()->setTimezone($employee->organisation->timezone?->name ?? config('app.timezone'))->toDateString();
    }

    /**
     * @param  array{start: Carbon, end: Carbon, unpaid_break_minutes: int}|array{}  $shift
     */
    private function isDayRunning(Employee $employee, string $date, array $shift): bool
    {
        if ($shift) {
            return now()->lessThan($shift['end']);
        }

        return $date >= now()->setTimezone($employee->organisation->timezone?->name ?? config('app.timezone'))->toDateString();
    }

    /**
     * @param  Collection<int, Collection<int, ManufactureBreak>>  $breaksByUser
     */
    private function hydrateDay(Production $production, Employee $employee, string $date, Collection $sessions, int $tolerance, ?ManufactureArtisanDay $day, Collection $breaksByUser): void
    {
        $schedule        = $this->schedule($employee);
        $shift           = $schedule ? $employee->scheduledShiftOn($date, $schedule) : null;
        $expectedMinutes = match (true) {
            $shift === null => null,
            $shift === []   => 0,
            default         => max(0, (int) round($shift['start']->diffInMinutes($shift['end']) - $shift['unpaid_break_minutes'])),
        };

        $isWaitingForTimesCheck = fn (ManufactureTaskSession $session) => $session->job_order_item_task_id !== null
            && $session->left_open_after_shift_at !== null
            && $session->shift_end_resolved_at === null;

        $isDayRunning    = $expectedMinutes !== null && $this->isDayRunning($employee, $date, $shift);
        $stillOpen       = $sessions->filter(fn (ManufactureTaskSession $session) => $session->state == ManufactureTaskSessionStateEnum::OPEN && !$isWaitingForTimesCheck($session));
        $runningJobs     = $sessions->filter($isWaitingForTimesCheck)->count() + ($isDayRunning ? 0 : $stillOpen->count());
        $recordedMinutes = (int) round($sessions
            ->filter(fn (ManufactureTaskSession $session) => $session->state == ManufactureTaskSessionStateEnum::CLOSED && !$isWaitingForTimesCheck($session))
            ->sum(fn (ManufactureTaskSession $session) => (float) ($session->hours ?? $session->paidHours())) * 60);

        $status = match (true) {
            $expectedMinutes === null                                   => ManufactureArtisanDayStatusEnum::NO_SHIFT,
            $runningJobs > 0                                            => ManufactureArtisanDayStatusEnum::RUNNING_JOB,
            $isDayRunning                                               => ManufactureArtisanDayStatusEnum::IN_PROGRESS,
            $recordedMinutes > $expectedMinutes + $tolerance            => ManufactureArtisanDayStatusEnum::OVER,
            $recordedMinutes < $expectedMinutes - $tolerance            => ManufactureArtisanDayStatusEnum::UNDER,
            $this->isMissingBreak($sessions, $shift, $breaksByUser)     => ManufactureArtisanDayStatusEnum::NO_BREAK,
            default                                                     => ManufactureArtisanDayStatusEnum::OK,
        };

        $day ??= new ManufactureArtisanDay([
            'group_id'        => $production->group_id,
            'organisation_id' => $production->organisation_id,
            'production_id'   => $production->id,
            'employee_id'     => $employee->id,
            'date'            => $date,
        ]);

        $day->fill([
            'user_id'                => $sessions->first()?->user_id ?? $day->user_id,
            'shift_start'            => isset($shift['start']) ? $shift['start']->copy()->setTimezone(config('app.timezone')) : null,
            'shift_end'              => isset($shift['end']) ? $shift['end']->copy()->setTimezone(config('app.timezone')) : null,
            'expected_minutes'       => $expectedMinutes,
            'recorded_minutes'       => $recordedMinutes,
            'tolerance_minutes'      => $tolerance,
            'unchecked_running_jobs' => $runningJobs,
            'status'                 => $status,
        ]);
        $day->is_warning_open = $day->needsAction();
        $day->save();
    }

    /**
     * A day with an unpaid break in the profile but no break clocked between its first job and its last. The break is
     * never deducted from pay: the day is flagged for a supervisor to check instead.
     *
     * @param  array{start: Carbon, end: Carbon, unpaid_break_minutes: int}|array{}  $shift
     * @param  Collection<int, Collection<int, ManufactureBreak>>  $breaksByUser
     */
    private function isMissingBreak(Collection $sessions, array $shift, Collection $breaksByUser): bool
    {
        if (($shift['unpaid_break_minutes'] ?? 0) <= 0 || $sessions->isEmpty()) {
            return false;
        }

        $firstStart = $sessions->min('started_at');
        $lastEnd    = $sessions->max(fn (ManufactureTaskSession $session) => $session->ended_at ?? now());

        return !$breaksByUser->get($sessions->first()->user_id, collect())
            ->contains(fn (ManufactureBreak $break) => $break->started_at->greaterThanOrEqualTo($firstStart) && $break->started_at->lessThan($lastEnd));
    }
}
