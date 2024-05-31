<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Jan 2024 17:29:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Fulfilment;

use Illuminate\Http\Resources\Json\JsonResource;

/**
*
 * @property int $id
 * @property int $reference
 */
class RecurringBillsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'reference'   => $this->reference,
        ];
    }
}
