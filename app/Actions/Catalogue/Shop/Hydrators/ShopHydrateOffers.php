<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 11 Sept 2024 22:14:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Shop\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Discounts\Offer\OfferStateEnum;
use App\Models\Catalogue\Shop;
use App\Models\Discounts\Offer;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class ShopHydrateOffers
{
    use AsAction;
    use WithEnumStats;

    private Shop $shop;

    public function __construct(Shop $shop)
    {
        $this->shop = $shop;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->shop->id))->dontRelease()];
    }

    public function handle(Shop $shop): void
    {
        $stats = [
            'number_offers'         => $shop->offers()->count(),
            'number_current_offers' => $shop->offers()->where('status', true)->count(),

        ];


        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'offers',
                field: 'state',
                enum: OfferStateEnum::class,
                models: Offer::class,
                where: function ($q) use ($shop) {
                    $q->where('shop_id', $shop->id);
                }
            )
        );


        $shop->discountsStats()->update($stats);
    }


}
