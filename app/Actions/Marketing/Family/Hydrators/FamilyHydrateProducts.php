<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 16:02:37 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Family\Hydrators;

use App\Actions\WithTenantJob;
use App\Models\Marketing\Family;
use App\Models\Marketing\Product;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class FamilyHydrateProducts implements ShouldBeUnique
{
    use AsAction;
    use WithTenantJob;

    public function handle(Family $family): void
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

    public function getJobUniqueId(Family $family): int
    {
        return $family->id;
    }
}
