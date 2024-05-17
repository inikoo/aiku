<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 23:14:46 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\HumanResources;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum EmployeeTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOWCASE                       = 'showcase';
    case HISTORY                        = 'history';
    case DATA                           = 'data';
    case TIMESHEETS                     = 'timesheets';
    case ATTACHMENTS                    = 'attachments';
    case IMAGES                         = 'images';
    case JOB_POSITIONS                  = 'job_positions';


    public function blueprint(): array
    {
        return match ($this) {
            EmployeeTabsEnum::IMAGES => [
                'title' => __('images'),
                'icon'  => 'fal fa-camera-retro',
                'type'  => 'icon',
                'align' => 'right',
            ],
            EmployeeTabsEnum::ATTACHMENTS => [
                'title' => __('attachments'),
                'icon'  => 'fal fa-paperclip',
                'type'  => 'icon',
                'align' => 'right',
            ],
            EmployeeTabsEnum::TIMESHEETS => [
                'title' => __('timesheets'),
                'icon'  => 'fal fa-stopwatch',
            ],
            EmployeeTabsEnum::JOB_POSITIONS => [
                'title' => __('job positions'),
                'icon'  => 'fal fa-network-wired',
                'type'  => 'icon',
                'align' => 'right',
            ],
            EmployeeTabsEnum::DATA => [
                'title' => __('database'),
                'icon'  => 'fal fa-database',
                'type'  => 'icon',
                'align' => 'right',
            ],
            EmployeeTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
            EmployeeTabsEnum::SHOWCASE => [
                'title' => __('employee'),
                'icon'  => 'fas fa-info-circle',
            ],
        };
    }
}
