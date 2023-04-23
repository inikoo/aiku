<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 18 Apr 2023 15:08:02 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\OfferCampaign;

use App\Models\Marketing\OfferCampaign;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreOfferCampaign
{
    use AsAction;

    public function handle(OfferCampaign $offerCampaign, array $modelData): OfferCampaign
    {
        return $offerCampaign->create($modelData);
    }
}
