<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 15 Feb 2022 22:35:27 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Marketing\Department;

use App\Actions\HydrateModel;
use App\Models\Marketing\Department;
use App\Models\Marketing\Family;
use App\Models\Marketing\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class HydrateDepartment extends HydrateModel
{
    public string $commandSignature = 'hydrate:department {tenants?*} {--i|id=} ';


    public function handle(Department $department): void
    {
        $this->familiesStats($department);
        $this->productsStats($department);
    }

    public function familiesStats(Department $department)
    {
        $familyStates  = ['in-process', 'active', 'discontinuing', 'discontinued'];
        $stateCounts   = Family::where('department_id', $department->id)
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();
        $stats         = [
            'number_families' => $department->families->count(),
        ];
        foreach ($familyStates as $familyState) {
            $stats['number_families_state_'.str_replace('-', '_', $familyState)] = Arr::get($stateCounts, $familyState, 0);
        }
        $department->stats->update($stats);
    }


    public function productsStats(Department $department)
    {
        $productStates = ['in-process', 'active', 'discontinuing', 'discontinued'];
        $stateCounts   = Product::where('department_id', $department->id)
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();
        $stats         = [
            'number_products' => $department->products->count(),
        ];
        foreach ($productStates as $productState) {
            $stats['number_products_state_'.str_replace('-', '_', $productState)] = Arr::get($stateCounts, $productState, 0);
        }
        $department->stats->update($stats);
    }




    protected function getModel(int $id): Department
    {
        return Department::find($id);
    }

    protected function getAllModels(): Collection
    {
        return Department::withTrashed()->get();
    }
}
