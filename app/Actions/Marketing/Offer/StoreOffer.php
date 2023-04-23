<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 18 Apr 2023 15:08:02 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Offer;

use App\Models\Marketing\Offer;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreOffer
{
    use AsAction;

    public function handle(Offer $offer, array $modelData): Offer
    {
        return $offer->create($modelData);
    }
}
