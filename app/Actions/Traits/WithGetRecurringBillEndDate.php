<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 13-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Traits;

use Carbon\CarbonInterface;
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
        if ($endDayOfMonth == 'last_day') {
            return Carbon::now()->endOfMonth();
        }


        if ($startDate->day > $endDayOfMonth) {
            // todo test this in a Unit Test
            // if today is  20th/10 and cut of day is 9 this must be 9th/11
            // if today is  7th/10 and cut of day is 9 this must be 9th/10

            $endDate = $startDate->copy()->addMonth()->day($endDayOfMonth);
        } else {
            $endDate = $startDate->copy()->day($endDayOfMonth);
        }

        $isWeekDays = $setting['is_weekdays'] ?? false;

        if ($isWeekDays) {
            while ($endDate->isWeekday()) {
                $endDate->addDay();
            }
        } else {
            while ($endDate->isWeekend()) {
                $endDate->addDay();
            }
        }

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
            'Sunday'    => CarbonInterface::SUNDAY,
            'Monday'    => CarbonInterface::MONDAY,
            'Tuesday'   => CarbonInterface::TUESDAY,
            'Wednesday' => CarbonInterface::WEDNESDAY,
            'Thursday'  => CarbonInterface::THURSDAY,
            'Friday'    => CarbonInterface::FRIDAY,
            'Saturday'  => CarbonInterface::SATURDAY,
        ];

        $endDayOfWeek = $daysOfWeek[$setting['day']];

        $endDate = $startDate->copy()->next($endDayOfWeek);

        if ($endDate->diffInDays($startDate) < 4) {
            $endDate = $endDate->addWeek();
        }

        return $endDate;
    }
}
