<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 11 Sept 2024 22:16:22 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Shop\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Discounts\OfferCampaign\OfferCampaignStateEnum;
use App\Models\Catalogue\Shop;
use App\Models\Discounts\OfferCampaign;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class ShopHydrateOfferCampaigns
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
            'number_offer_campaigns'         => $shop->offerCampaigns()->count(),
            'number_current_offer_campaigns' => $shop->offerCampaigns()->where('status', true)->count(),

        ];


        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'offer_campaigns',
                field: 'state',
                enum: OfferCampaignStateEnum::class,
                models: OfferCampaign::class,
                where: function ($q) use ($shop) {
                    $q->where('shop_id', $shop->id);
                }
            )
        );

        $shop->discountsStats()->update($stats);
    }


}
