<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 27 Nov 2024 17:49:05 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Discounts\OfferComponent;

use App\Actions\Discounts\OfferComponent\Hydrators\OfferComponentHydrateInvoices;
use App\Actions\Discounts\OfferComponent\Hydrators\OfferComponentHydrateOrders;
use App\Actions\HydrateModel;
use App\Models\Discounts\OfferCampaign;
use App\Models\Discounts\OfferComponent;

class HydrateOfferComponents extends HydrateModel
{
    public string $commandSignature = 'hydrate:offer_components {organisations?*} {--s|slugs=}';


    public function handle(OfferComponent $offerComponent): void
    {
        OfferComponentHydrateOrders::run($offerComponent);
        OfferComponentHydrateInvoices::run($offerComponent);
    }

    protected function getModel(string $slug): OfferCampaign
    {
        return OfferComponent::where('slug', $slug)->first();
    }

    protected function getAllModels(): \Illuminate\Support\Collection
    {
        return OfferComponent::withTrashed()->get();
    }
}
