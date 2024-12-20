<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 15-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Discounts\OfferCampaign\Search;

use App\Actions\HydrateModel;
use App\Models\Discounts\OfferCampaign;
use Illuminate\Support\Collection;

class ReindexOfferCampaignSearch extends HydrateModel
{
    public string $commandSignature = 'search:offer_campaigns {organisations?*} {--s|slugs=}';


    public function handle(OfferCampaign $offerCampaign): void
    {
        OfferCampaignRecordSearch::run($offerCampaign);
    }

    protected function getModel(string $slug): OfferCampaign
    {
        return OfferCampaign::withTrashed()->where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return OfferCampaign::withTrashed()->get();
    }
}
