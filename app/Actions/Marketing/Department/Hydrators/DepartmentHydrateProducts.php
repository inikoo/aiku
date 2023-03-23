<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 24 Mar 2023 05:16:20 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Department\Hydrators;

use App\Actions\WithTenantJob;
use App\Enums\Marketing\Product\ProductStateEnum;
use App\Models\Marketing\Department;
use App\Models\Marketing\Product;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class DepartmentHydrateProducts implements ShouldBeUnique
{
    use AsAction;
    use WithTenantJob;

    public function handle(Department $department): void
    {
        $stats         = [
            'number_products' => $department->products->count(),
        ];
        $stateCounts   = Product::where('department_id', $department->id)
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();

        foreach (ProductStateEnum::cases() as $productState) {
            $stats['number_products_state_'.$productState->snake()] = Arr::get($stateCounts, $productState->value, 0);
        }
        $department->stats->update($stats);
    }

    public function getJobUniqueId(Department $department): int
    {
        return $department->id;
    }
}
