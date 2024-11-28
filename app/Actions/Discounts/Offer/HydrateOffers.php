<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 27 Nov 2024 18:04:45 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Discounts\Offer;

use App\Actions\Discounts\Offer\Hydrators\OfferHydrateInvoices;
use App\Actions\Discounts\Offer\Hydrators\OfferHydrateOrders;
use App\Actions\HydrateModel;
use App\Models\Discounts\Offer;
use App\Models\Discounts\OfferCampaign;

class HydrateOffers extends HydrateModel
{
    public string $commandSignature = 'hydrate:offers {organisations?*} {--s|slugs=}';


    public function handle(Offer $offer): void
    {
        OfferHydrateInvoices::run($offer);
        OfferHydrateOrders::run($offer);
    }

    protected function getModel(string $slug): OfferCampaign
    {
        return Offer::where('slug', $slug)->first();
    }

    protected function getAllModels(): \Illuminate\Support\Collection
    {
        return Offer::withTrashed()->get();
    }
}
