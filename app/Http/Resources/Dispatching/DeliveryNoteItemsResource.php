<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 22 Feb 2023 22:40:34 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Dispatching;

use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryNoteItemsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                  => $this->id,
            'state'               => $this->state,
            'status'              => $this->status,
            'quantity_required'   => $this->quantity_required,
            'quantity_picked'     => $this->quantity_picked,
            'quantity_packed'     => $this->quantity_packed,
            'quantity_dispatched' => $this->quantity_dispatched,
            'org_stock_code'      => $this->org_stock_code,
            'org_stock_name'      => $this->org_stock_name
        ];
    }
}
