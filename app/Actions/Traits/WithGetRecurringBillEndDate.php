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

trait WithGetRecurringBillEndDate
{
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
            $endDate = $startDate->copy()->addMonth()->day($endDayOfMonth);
        } else {
            $endDate = $startDate->copy()->day($endDayOfMonth);
        }

        $isWeekDays = $setting['is_weekdays'] ?? null;

        if ($isWeekDays) {
            if ($isWeekDays == true) {
                while ($endDate->isWeekday()) {
                    $endDate->addDay();
                }
            } else {
                while ($endDate->isWeekend()) {
                    $endDate->addDay();
                }
            }
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

        return $endDate;
    }
}
