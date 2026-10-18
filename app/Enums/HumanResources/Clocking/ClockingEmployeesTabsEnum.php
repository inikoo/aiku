<?php

namespace App\Enums\HumanResources\Clocking;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum ClockingEmployeesTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case CLOCK_IN_OUT = 'clock_in_out';
    case TIMESHEETS = 'timesheets';
    case LEAVES = 'leaves';
    case ADJUSTMENTS = 'adjustments';
    case OVERTIME = 'overtime';
    case CALENDAR = 'calendar';

    public function blueprint(): array
    {
        return match ($this) {
            ClockingEmployeesTabsEnum::CLOCK_IN_OUT => [
                'title' => __('Clock In / Out'),
                'icon' => 'fal fa-user-clock',
            ],
            ClockingEmployeesTabsEnum::TIMESHEETS => [
                'icon' => 'fal fa-stopwatch',
                'title' => __('Timesheets'),
            ],
            ClockingEmployeesTabsEnum::LEAVES => [
                'icon' => 'fal fa-calendar-minus',
                'title' => __('Leave'),
            ],
            ClockingEmployeesTabsEnum::ADJUSTMENTS => [
                'icon' => 'fal fa-clock',
                'icon_layers' => ['fal fa-clock', 'fal fa-pencil'],
                'title' => __('Clocking adjustment'),
            ],
            ClockingEmployeesTabsEnum::OVERTIME => [
                'icon' => 'fal fa-clock',
                'title' => __('Overtime'),
            ],
            ClockingEmployeesTabsEnum::CALENDAR => [
                'icon' => 'fal fa-calendar',
                'title' => __('Calendar'),
            ],
        };
    }
}
