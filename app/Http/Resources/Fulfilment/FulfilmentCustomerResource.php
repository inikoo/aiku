<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 26 Feb 2024 19:57:44 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Fulfilment;

use App\Http\Resources\HasSelfCall;
use App\Models\Fulfilment\FulfilmentCustomer;
use Illuminate\Http\Resources\Json\JsonResource;

class FulfilmentCustomerResource extends JsonResource
{
    use HasSelfCall;

    public function toArray($request): array
    {
        /** @var FulfilmentCustomer $fulfilmentCustomer */
        $fulfilmentCustomer = $this;

        return [
            'number_pallets'                => $fulfilmentCustomer->number_pallets,
            'number_pallets_state_received' => $fulfilmentCustomer->number_pallets_state_received,
            'number_stored_items'           => $fulfilmentCustomer->number_stored_items,
            'number_pallet_deliveries'      => $fulfilmentCustomer->number_pallet_deliveries,
            'number_pallet_returns'         => $fulfilmentCustomer->number_pallet_returns,

        ];
    }
}
