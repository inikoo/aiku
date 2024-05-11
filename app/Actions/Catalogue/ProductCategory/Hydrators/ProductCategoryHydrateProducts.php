<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 24 Mar 2023 05:16:20 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\ProductCategory\Hydrators;

use App\Enums\Catalogue\Product\ProductStateEnum;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\ProductCategory;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class ProductCategoryHydrateProducts implements ShouldBeUnique
{
    use AsAction;


    public function handle(ProductCategory $productCategory): void
    {
        $stats         = [
            'number_products' => $productCategory->products->count(),
        ];
        $stateCounts   = Product::where('productCategory_id', $productCategory->id)
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();

        foreach (ProductStateEnum::cases() as $productState) {
            $stats['number_products_state_'.$productState->snake()] = Arr::get($stateCounts, $productState->value, 0);
        }
        $productCategory->stats()->update($stats);
    }

    public function getJobUniqueId(ProductCategory $productCategory): int
    {
        return $productCategory->id;
    }
}
