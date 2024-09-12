<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:32:25 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Discounts\OfferCampaign;

use App\Actions\Discounts\OfferCampaign\Hydrators\OfferCampaignHydrateOffers;
use App\Actions\HydrateModel;
use App\Models\Discounts\OfferCampaign;
use Illuminate\Support\Collection;

class HydrateOfferCampaigns extends HydrateModel
{
    public string $commandSignature = 'hydrate:offer-campaigns {organisations?*} {--s|slugs=}';


    public function handle(OfferCampaign $offerCampaign): void
    {
        OfferCampaignHydrateOffers::run($offerCampaign);
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
