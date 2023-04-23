<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 18 Apr 2023 15:08:02 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\OfferComponent;

use App\Models\Marketing\OfferComponent;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreOfferComponent
{
    use AsAction;

    public function handle(OfferComponent $offerComponent, array $modelData): OfferComponent
    {
        return $offerComponent->create($modelData);
    }
}
