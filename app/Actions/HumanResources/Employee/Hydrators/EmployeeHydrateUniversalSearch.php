<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\HumanResources\Employee\Hydrators;

use App\Actions\WithRoutes;
use App\Actions\WithTenantJob;
use App\Models\HumanResources\Employee;
use Lorisleiva\Actions\Concerns\AsAction;

class EmployeeHydrateUniversalSearch
{
    use AsAction;
    use WithTenantJob;
    use WithRoutes;

    public function handle(Employee $employee): void
    {

        $employee->universalSearch()->create(
            [
                'section' => 'HumanResources',
                'route' => $this->routes(),
                'icon' => 'fa-user-hard-hat',
                'primary_term'   => $employee->contact_name,
                'secondary_term' => $employee->email
            ]
        );
    }


}
