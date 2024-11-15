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
use App\Models\Discounts\Offer;
use Illuminate\Support\Collection;

class ReindexOfferCampaignSearch extends HydrateModel
{
    public string $commandSignature = 'offer:search {organisations?*} {--s|slugs=}';


    public function handle(Offer $offer): void
    {
        OfferCampaignRecordSearch::run($offer);
    }

    protected function getModel(string $slug): Offer
    {
        return Offer::withTrashed()->where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Offer::withTrashed()->get();
    }
}
