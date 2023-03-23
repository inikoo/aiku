<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Marketing\Department\Hydrators;

use App\Actions\WithTenantJob;
use App\Enums\Marketing\Family\FamilyStateEnum;
use App\Models\Marketing\Department;
use App\Models\Marketing\Family;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class DepartmentHydrateFamilies implements ShouldBeUnique
{
    use AsAction;
    use WithTenantJob;

    public function handle(Department $department): void
    {
        $stats         = [
            'number_families' => $department->families->count(),
        ];
        $stateCounts   = Family::where('department_id', $department->id)
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();

        foreach (FamilyStateEnum::cases() as $familyState) {
            $stats['number_families_state_'.$familyState->snake()] = Arr::get($stateCounts, $familyState->value, 0);
        }
        $department->stats->update($stats);
    }

    public function getJobUniqueId(Department $department): int
    {
        return $department->id;
    }
}
