<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 18 Dec 2024 23:47:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\UI\Catalogue\Rentals;

use App\Models\Billables\Rental;
use Lorisleiva\Actions\Concerns\AsObject;

class GetRentalShowcase
{
    use AsObject;

    public function handle(Rental $rental)
    {
        // dd($rental);
        return [
            'id' => $rental->id,
            'name' => $rental->name,
            'description' => $rental->description,
            'code' => $rental->code,
            'price' => (float) $rental->price,
            'currency' => $rental->currency,
            'unit' => $rental->unit,
            'units' => (int) $rental->units,
            'status' => $rental->status,
            'state' => $rental->state,
            'created_at' => $rental->created_at,
        ];
    }
}
