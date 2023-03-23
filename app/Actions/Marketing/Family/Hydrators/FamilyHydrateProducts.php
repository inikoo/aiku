<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 16:02:37 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Family\Hydrators;

use App\Actions\WithTenantJob;
use App\Enums\Marketing\Product\ProductStateEnum;
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
        $stats         = [
            'number_products' => $family->products->count(),
        ];
        $stateCounts   = Product::where('family_id', $family->id)
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();

        foreach (ProductStateEnum::cases() as $productState) {
            $stats['number_products_state_'.$productState->snake()] = Arr::get($stateCounts, $productState->value, 0);
        }
        $family->stats->update($stats);
    }

    public function getJobUniqueId(Family $family): int
    {
        return $family->id;
    }
}
