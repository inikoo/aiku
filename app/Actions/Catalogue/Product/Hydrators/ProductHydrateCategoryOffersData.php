<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Oct 2026 20:45:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Product\Hydrators;

use App\Actions\Discounts\Offer\UpdateProductCategoryOffersData;
use App\Actions\Traits\Hydrators\WithHydrateCommand;
use App\Models\Catalogue\Product;
use App\Models\Discounts\Offer;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Arr;

class ProductHydrateCategoryOffersData implements ShouldBeUnique
{
    use WithHydrateCommand;

    public string $commandSignature = 'hydrate:product-category-offers-data {organisations?*} {--s|slugs=} {--ids=} {--shop=}';

    public function __construct()
    {
        $this->model = Product::class;
    }

    public function getJobUniqueId(Product $product): string
    {
        return $product->id;
    }

    public function handle(Product $product): void
    {
        $categoryIds   = array_filter([$product->family_id, $product->department_id, $product->sub_department_id]);
        $offersUpdater = UpdateProductCategoryOffersData::make();
        $cachedOfferIds = array_keys(Arr::get($product->offers_data, 'offers', []));

        $cachedOffers = Offer::withTrashed()->whereIn('id', $cachedOfferIds)->get()->keyBy('id');
        foreach ($cachedOfferIds as $offerId) {
            $offer = $cachedOffers->get($offerId) ?? (new Offer())->forceFill(['id' => $offerId]);
            if (!$offer->status || ($offer->trigger_type == 'ProductCategory' && !array_intersect($offer->triggerCategoryIds(), $categoryIds))) {
                $offersUpdater->updateModelOfferData($product, null, $offer);
            }
        }

        $liveCategoryOffers = Offer::where('shop_id', $product->shop_id)
            ->where('trigger_type', 'ProductCategory')
            ->where('status', true)
            ->whereNotIn('id', $cachedOfferIds)
            ->get()
            ->filter(fn (Offer $offer) => array_intersect($offer->triggerCategoryIds(), $categoryIds));

        foreach ($liveCategoryOffers as $offer) {
            $offersUpdater->updateModelOfferData($product, $offersUpdater->getBasicOfferData($offer), $offer);
        }
    }
}
