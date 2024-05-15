<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 23:14:46 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

 namespace App\Enums\UI\SysAdmin;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum ProfileTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOWCASE                       = 'showcase';
    case HISTORY                        = 'history';
    case VISIT_LOGS                     = 'visit_logs';
    // case DATA                           = 'data';
    // case TODAY_TIMESHEETS               = 'today_timesheets';
    case TIMESHEETS                     = 'timesheets';
    // case ATTACHMENTS                    = 'attachments';
    // case IMAGES                         = 'images';
    // case JOB_POSITIONS                  = 'job_positions';


    public function blueprint(): array
    {
        return match ($this) {
            // ProfileTabsEnum::TODAY_TIMESHEETS => [
            //     'title' => __('today time sheets'),
            //     'icon'  => 'fal fa-database',
            // ],
            ProfileTabsEnum::VISIT_LOGS => [
                'title' => __('visit logs'),
                'icon'  => 'fal fa-database',
            ],
            // EmployeeTabsEnum::IMAGES => [
            //     'title' => __('images'),
            //     'icon'  => 'fal fa-camera-retro',
            //     'type'  => 'icon',
            //     'align' => 'right',
            // ],
            // EmployeeTabsEnum::ATTACHMENTS => [
            //     'title' => __('attachments'),
            //     'icon'  => 'fal fa-paperclip',
            //     'type'  => 'icon',
            //     'align' => 'right',
            // ],
            ProfileTabsEnum::TIMESHEETS => [
                'title' => __('time sheets'),
                'icon'  => 'fal fa-database',
            ],
            // EmployeeTabsEnum::JOB_POSITIONS => [
            //     'title' => __('job positions'),
            //     'icon'  => 'fal fa-network-wired',
            //     'type'  => 'icon',
            //     'align' => 'right',
            // ],
            // EmployeeTabsEnum::DATA => [
            //     'title' => __('database'),
            //     'icon'  => 'fal fa-database',
            //     'type'  => 'icon',
            //     'align' => 'right',
            // ],
            ProfileTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
            ProfileTabsEnum::SHOWCASE => [
                'title' => __('Profile'),
                'icon'  => 'fas fa-info-circle',
            ],
        };
    }
}
