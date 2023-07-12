<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\HumanResources\Employee\Hydrators;

use App\Actions\Traits\WithTenantJob;
use App\Models\HumanResources\Employee;
use Lorisleiva\Actions\Concerns\AsAction;

class EmployeeHydrateUniversalSearch
{
    use AsAction;
    use WithTenantJob;

    public function handle(Employee $employee): void
    {

        $employee->universalSearch()->create(
            [
                'section' => 'HumanResources',
                'route'   => json_encode([
                    'name'      => 'hr.employees.show',
                    'arguments' => [
                        $employee->slug
                    ]
                ]),
                'icon'           => 'fa-user-hard-hat',
                'primary_term'   => $employee->contact_name,
                'secondary_term' => $employee->email
            ]
        );
    }


}
