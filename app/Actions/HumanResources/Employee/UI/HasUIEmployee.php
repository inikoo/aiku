<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Mar 2023 19:09:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee\UI;

use App\Actions\UI\HumanResources\HumanResourcesDashboard;
use App\Models\HumanResources\Employee;

trait HasUIEmployee
{
    public function getBreadcrumbs(Employee $employee): array
    {
        return array_merge(
            (new HumanResourcesDashboard())->getBreadcrumbs(),
            [
                'hr.employees.show' => [
                    'route'           => 'hr.employees.show',
                    'routeParameters' => $employee->id,
                    'name'            => $employee->slug,
                    'index'           => [
                        'route'   => 'hr.employees.index',
                        'overlay' => __('Employees list')
                    ],
                    'modelLabel'      => [
                        'label' => __('employee')
                    ],
                ],
            ]
        );
    }
}
