<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:37:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Invoice\Hydrators;

use App\Actions\Traits\WithActionUpdate;
use App\Models\Fulfilment\Pallet;
use App\Models\FulfilmentCustomer;
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
