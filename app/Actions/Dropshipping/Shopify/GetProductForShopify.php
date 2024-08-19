<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 11 Jul 2024 10:16:14 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Shopify;

use App\Enums\Catalogue\Product\ProductStateEnum;
use App\Models\Catalogue\Product;
use App\Models\Dropshipping\ShopifyUser;
use App\Services\QueryBuilder;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Spatie\QueryBuilder\AllowedFilter;

class GetProductForShopify
{
    use AsAction;
    use WithAttributes;

    /**
     * @throws \Exception
     */
    public function handle(ShopifyUser $shopifyUser)
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('products.name', $value)
                    ->orWhereStartWith('products.code', $value);
            });
        });

        $queryBuilder = QueryBuilder::for(Product::class);

        $queryBuilder->where('shop_id', $shopifyUser->customer->shop_id);
        $queryBuilder->where('state', ProductStateEnum::ACTIVE->value);

        $queryBuilder
            ->defaultSort('products.code');

        return $queryBuilder->allowedSorts(['code', 'name', 'shop_slug'])
            ->allowedFilters([$globalSearch])
            ->withPaginator(null)
            ->withQueryString();
    }

    public function asController(ShopifyUser $shopifyUser, ActionRequest $request)
    {
        return $this->handle($shopifyUser);
    }
}
