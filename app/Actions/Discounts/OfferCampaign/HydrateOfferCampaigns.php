<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:32:25 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Discounts\OfferCampaign;

use App\Actions\Discounts\OfferCampaign\Hydrators\OfferCampaignHydrateInvoices;
use App\Actions\Discounts\OfferCampaign\Hydrators\OfferCampaignHydrateOffers;
use App\Actions\Discounts\OfferCampaign\Hydrators\OfferCampaignHydrateOrders;
use App\Actions\HydrateModel;
use App\Models\Discounts\OfferCampaign;
use Illuminate\Support\Collection;

class HydrateOfferCampaigns extends HydrateModel
{
    public string $commandSignature = 'hydrate:offer_campaigns {organisations?*} {--s|slugs=}';


    public function handle(OfferCampaign $offerCampaign): void
    {
        OfferCampaignHydrateInvoices::run($offerCampaign);
        OfferCampaignHydrateOffers::run($offerCampaign);
        OfferCampaignHydrateOrders::run($offerCampaign);
    }

    protected function getModel(string $slug): OfferCampaign
    {
        return OfferCampaign::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return OfferCampaign::withTrashed()->get();
    }
}
