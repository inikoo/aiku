<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 13-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

trait WithGetRecurringBillEndDate
{
    use AsAction;

    public function getEndDate(Carbon $startDate, array $setting): Carbon
    {
        return match (Arr::get($setting, 'type')) {
            'weekly' => $this->getEndDateWeekly($startDate, $setting),
            default => $this->getEndDateMonthly($startDate, $setting),
        };
    }

    public function getEndDateMonthly(Carbon $startDate, array $setting): Carbon
    {
        $endDayOfMonth = $setting['day'];

        $endDate = $startDate->copy()->day($endDayOfMonth);

        if ($endDate->lt($startDate)) {
            $endDate->addMonth();
        }

        if ($endDate->diffInDays($startDate) < 4) {
            $endDate->addMonth();
        }

        return $endDate;
    }

    public function getEndDateWeekly(Carbon $startDate, array $setting): Carbon
    {
        $daysOfWeek = [
            'Sunday'    => Carbon::SUNDAY,
            'Monday'    => Carbon::MONDAY,
            'Tuesday'   => Carbon::TUESDAY,
            'Wednesday' => Carbon::WEDNESDAY,
            'Thursday'  => Carbon::THURSDAY,
            'Friday'    => Carbon::FRIDAY,
            'Saturday'  => Carbon::SATURDAY,
        ];

        $endDayOfWeek = $daysOfWeek[$setting['day']];

        $endDate = $startDate->copy()->next($endDayOfWeek);

        if ($endDate->diffInDays($startDate) < 4) {
            $endDate = $endDate->addWeek();
        }

        return $endDate;
    }
}
