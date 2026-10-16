<?php

/*
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Masters\MasterProductCategory\UI;

use App\Enums\Catalogue\ProductCategory\ProductCategoryStateEnum;
use App\Enums\Catalogue\Shop\ShopStateEnum;
use App\Models\Masters\MasterProductCategory;
use Lorisleiva\Actions\Concerns\AsObject;

class GetMasterFamilyShopsStatus
{
    use AsObject;

    /**
     * @return array{total: int, states: array<int, array{state: string, label: string, count: int, shops: array<int, string>}>}
     */
    public function handle(MasterProductCategory $masterFamily): array
    {
        $shopFamilies = $masterFamily->productCategories()
            ->join('shops', 'shops.id', '=', 'product_categories.shop_id')
            ->where('shops.state', '!=', ShopStateEnum::CLOSED)
            ->orderBy('shops.code')
            ->get(['product_categories.state', 'shops.code as shop_code']);

        $labels = ProductCategoryStateEnum::labels();

        $states = collect(ProductCategoryStateEnum::cases())
            ->map(function (ProductCategoryStateEnum $state) use ($shopFamilies, $labels) {
                $shops = $shopFamilies->where('state', $state)->pluck('shop_code')->values()->all();

                return [
                    'state' => $state->value,
                    'label' => $labels[$state->value],
                    'count' => count($shops),
                    'shops' => $shops,
                ];
            })
            ->filter(fn (array $state) => $state['count'] > 0)
            ->values()
            ->all();

        return [
            'total'  => $shopFamilies->count(),
            'states' => $states,
        ];
    }
}
