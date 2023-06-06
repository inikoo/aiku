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
            'hired'         => 'Hired',
            'working'       => 'Working',
            'left'          => 'Left',
        ];
    }
}
