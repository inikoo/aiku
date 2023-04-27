<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 18 Apr 2023 15:08:02 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Offer;

use App\Models\Marketing\Offer;
use App\Models\Marketing\OfferCampaign;
use App\Models\Marketing\Shop;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreOffer
{
    use AsAction;

    public function handle(Shop $shop, OfferCampaign $offerCampaign, array $modelData): Offer
    {
        $modelData['shop_id'] = $shop->id;
        /** @var Offer $offer */
        $offer = $offerCampaign->offers()->create($modelData);

        return $offer;
    }
}
