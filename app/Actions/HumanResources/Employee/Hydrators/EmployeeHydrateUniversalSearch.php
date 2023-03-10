<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\HumanResources\Employee\Hydrators;

use App\Actions\WithTenantJob;
use App\Models\HumanResources\Employee;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class EmployeeHydrateUniversalSearch implements ShouldBeUnique
{
    use AsAction;
    use WithTenantJob;

    public function handle(Employee $employee): void
    {
        $employee->universalSearch()->create(
            [
                'primary_term'   => $employee->name,
                'secondary_term' => $employee->email
            ]
        );
    }

    public function getJobUniqueId(Employee $employee): int
    {
        return $employee->id;
    }
}
