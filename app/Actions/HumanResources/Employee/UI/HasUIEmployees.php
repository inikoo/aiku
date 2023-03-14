<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Mar 2023 19:09:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee\UI;

use App\Actions\UI\HumanResources\HumanResourcesDashboard;

trait HasUIEmployees
{
    public function getBreadcrumbs(): array
    {
        return array_merge(
            (new HumanResourcesDashboard())->getBreadcrumbs(),
            [
                'hr.employees.index' => [
                    'route'      => 'hr.employees.index',
                    'modelLabel' => [
                        'label' => __('employees')
                    ],
                ],
            ]
        );
    }
}
