<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Marketing\Department\Hydrators;

use App\Actions\WithTenantJob;
use App\Models\Marketing\Department;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class DepartmentHydrateUniversalSearch implements ShouldBeUnique
{
    use AsAction;
    use WithTenantJob;

    public function handle(Department $department): void
    {
        $department->universalSearch()->create(
            [
                'primary_term'   => $department->name,
                'secondary_term' => $department->code
            ]
        );
    }

    public function getJobUniqueId(Department $department): int
    {
        return $department->id;
    }
}
