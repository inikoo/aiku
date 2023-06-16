<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 16 Jun 2023 11:39:33 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum JobPositionTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOWCASE                       = 'showcase';
    case HISTORY                        = 'history';
    case DATA                           = 'data';
    case EMPLOYEES                      = 'employees';
    case ROLES                          = 'roles';

    public function blueprint(): array
    {
        return match ($this) {

            JobPositionTabsEnum::ROLES => [
                'title' => __('system roles'),
                'icon'  => 'fal fa-terminal',
            ],
            JobPositionTabsEnum::EMPLOYEES => [
                'title' => __('employees'),
                'icon'  => 'fal fa-user-hard-hat',
            ],
            JobPositionTabsEnum::DATA => [
                'title' => __('database'),
                'icon'  => 'fal fa-database',
                'type'  => 'icon',
                'align' => 'right',
            ],
            JobPositionTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
            JobPositionTabsEnum::SHOWCASE => [
                'title' => __('job position'),
                'icon'  => 'fas fa-info-circle',
            ],
        };
    }
}
