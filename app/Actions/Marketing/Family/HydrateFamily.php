<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 15 Feb 2022 22:39:06 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Marketing\Family;

use App\Actions\HydrateModel;
use App\Models\Marketing\Family;
use App\Models\Marketing\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class HydrateFamily extends HydrateModel
{
    public string $commandSignature = 'hydrate:family {tenants?*} {--i|id=} ';

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


    protected function getModel(int $id): Family
    {
        return Family::find($id);
    }

    protected function getAllModels(): Collection
    {
        return Family::withTrashed()->get();
    }
}
