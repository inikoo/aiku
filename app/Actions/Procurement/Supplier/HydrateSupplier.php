<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 14:41:22 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Supplier;

use App\Actions\HydrateModel;
use App\Models\Marketing\Family;
use App\Models\Marketing\Product;
use App\Models\Procurement\Supplier;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;


class HydrateSupplier extends HydrateModel
{

    public string $commandSignature = 'hydrate:supplier {tenants?*} {--i|id=} ';

    public function handle(Family $family): void
    {
        $this->productsStats($family);
    }

    public function productsStats(Family $family)
    {
        $productStates = ['in-process', 'active', 'discontinuing', 'discontinued'];
        $stateCounts   = Product::where('family_id', $family->id)
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();
        $stats         = [
            'number_products' => $family->products->count(),
        ];
        foreach ($productStates as $productState) {
            $stats['number_products_state_'.str_replace('-', '_', $productState)] = Arr::get($stateCounts, $productState, 0);
        }
        $family->stats->update($stats);
    }


    protected function getModel(int $id): Supplier
    {
        return Supplier::find($id);
    }

    protected function getAllModels(): Collection
    {
        return Supplier::withTrashed()->get();
    }


}


