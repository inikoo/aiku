<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 25 Jan 2024 16:42:23 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentCustomer\Hydrators;

use App\Actions\Traits\WithActionUpdate;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;

use Lorisleiva\Actions\Concerns\AsAction;

class HydrateFulfilmentCustomer
{
    use AsAction;
    use WithActionUpdate;

    public function handle(FulfilmentCustomer $fulfilmentCustomer): mixed
    {
        return $this->update($fulfilmentCustomer, [
            'number_pallets' => Pallet::where('fulfilment_customer_id', $fulfilmentCustomer->id)->count()
        ]);
    }
}
