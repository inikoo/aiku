<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Mar 2023 02:20:41 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\HumanResources\Employee;

use App\Enums\EnumHelperTrait;

enum EmployeeStateEnum: string
{
    use EnumHelperTrait;

    case HIRED   = 'hired';
    case WORKING = 'working';
    case LEFT    = 'left';

    public static function labels(): array
    {
        return [
            'hired'         => __('Hired'),
            'working'       => __('Working'),
            'left'          => __('Left'),
        ];
    }

    public static function count(): array
    {
        $stats=app('currentTenant')->stats;
        return [
            'hired'         => $stats->number_employees_state_hired,
            'working'       => $stats->number_employees_state_working,
            'left'          => $stats->number_employees_state_left,
        ];
    }


}
