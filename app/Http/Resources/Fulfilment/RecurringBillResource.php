<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 18:24:37 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Fulfilment;

use App\Models\Fulfilment\RecurringBill;
use Illuminate\Http\Resources\Json\JsonResource;

class RecurringBillResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var RecurringBill $recurringBill */
        $recurringBill = $this;

        return [
            'id'     => $recurringBill->id,
            'slug'   => $recurringBill->slug,
        ];
    }
}
