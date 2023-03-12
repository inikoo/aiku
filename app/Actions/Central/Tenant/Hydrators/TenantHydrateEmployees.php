<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 02 Mar 2023 19:02:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Tenant\Hydrators;

use App\Enums\HumanResources\Employee\EmployeeStateEnum;
use App\Models\Central\Tenant;
use App\Models\HumanResources\Employee;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class TenantHydrateEmployees implements ShouldBeUnique
{
    use AsAction;
    use HasTenantHydrate;

    public function handle(Tenant $tenant): void
    {
        $stats = [
            'number_employees' => Employee::count()
        ];


        $employeeStateCount = Employee::selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();


        foreach (EmployeeStateEnum::cases() as $employeeState) {
            $stats['number_employees_state_'.$employeeState->snake()] = Arr::get($employeeStateCount, $employeeState->value, 0);
        }

        $tenant->stats->update($stats);
    }
}
