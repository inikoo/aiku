<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 07 Oct 2026 17:30:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Product\Json;

use App\Actions\OrgAction;
use App\Enums\Catalogue\Product\ProductStateEnum;
use App\Enums\Inventory\OrgStock\OrgStockStateEnum;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\ProductCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\ActionRequest;

class GetDiscontinuingProductsInFamily extends OrgAction
{
    /**
     * Discontinued products that still have stock, cheapest first: what a clearance gift gives away.
     * Discontinuing is usually set on the SKOs, not the product, so a product whose SKOs are all discontinuing counts too.
     *
     * @return Collection<int, Product>
     */
    public function handle(ProductCategory $family): Collection
    {
        $discontinuingStates = [OrgStockStateEnum::DISCONTINUING, OrgStockStateEnum::DISCONTINUED];

        return Product::where('family_id', $family->id)
            ->whereIn('state', [ProductStateEnum::ACTIVE, ProductStateEnum::DISCONTINUING])
            ->where('is_for_sale', true)
            ->whereNull('exclusive_for_customer_id')
            ->where('is_on_demand', false)
            ->where(function (Builder $query) use ($discontinuingStates) {
                $query->where('state', ProductStateEnum::DISCONTINUING)
                    ->orWhere(function (Builder $query) use ($discontinuingStates) {
                        $query->whereHas('orgStocks')
                            ->whereDoesntHave('orgStocks', fn (Builder $orgStocks) => $orgStocks->whereNotIn('org_stocks.state', $discontinuingStates));
                    });
            })
            ->where('available_quantity', '>', 0)
            ->orderBy('price')
            ->orderBy('id')
            ->get();
    }

    /**
     * What a clearance gift of $quantity hands out: cheapest first, spilling to the next product when one runs short.
     * Also says whether that takes the last of the stock, so the offer can finish (HELP-3794).
     *
     * @return array{0: array<int, array{0: Product, 1: int}>, 1: bool}
     */
    public function getGiftLines(?ProductCategory $family, ?int $productId, int $quantity): array
    {
        $candidates = $family ? $this->handle($family) : collect();
        if ($productId) {
            $candidates = $candidates->where('id', $productId);
        }

        $giftLines = [];
        $remaining = $quantity;
        foreach ($candidates as $product) {
            if ($remaining <= 0) {
                break;
            }
            $giftQuantity = min($remaining, (int)$product->available_quantity);
            $giftLines[]  = [$product, $giftQuantity];
            $remaining    -= $giftQuantity;
        }

        return [$giftLines, $candidates->sum('available_quantity') <= $quantity - $remaining];
    }

    public function jsonResponse(Collection $products): array
    {
        return $products->map(fn (Product $product) => [
            'id'                 => $product->id,
            'code'               => $product->code,
            'name'               => $product->name,
            'label'              => $product->code.' '.$product->name.' ('.(int)$product->available_quantity.')',
            'price'              => $product->price,
            'available_quantity' => (int)$product->available_quantity,
        ])->all();
    }

    public function asController(ProductCategory $productCategory, ActionRequest $request): Collection
    {
        $this->initialisationFromShop($productCategory->shop, $request);

        return $this->handle($productCategory);
    }
}
