<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 10 Jun 2024 11:53:47 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Discounts\OfferCampaign\UI;

use App\Http\Resources\Catalogue\OfferCampaignResource;
use App\Http\Resources\Catalogue\ProductResource;
use App\Models\Catalogue\Product;
use App\Models\Discounts\OfferCampaign;
use Lorisleiva\Actions\Concerns\AsObject;

class GetOfferCampaignOverview
{
    use AsObject;

    public function handle(OfferCampaign $offerCampaign): array
    {
        return [
            'offerCampaign' => OfferCampaignResource::make($offerCampaign),
            'stats'   => $offerCampaign->stats
        ];
    }
}
