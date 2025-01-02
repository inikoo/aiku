<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 02-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Http\Resources\Inventory;

use Illuminate\Http\Resources\Json\JsonResource;

class OrgStockMovementsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'class' => $this->class,
            'type' => $this->type,
            'flow' => $this->flow,
            'quantity' => $this->quantity,
            'org_stock_name' => $this->org_stock_name,
            'org_amount' => $this->org_amount,
            'organisation_name' => $this->organisation_name,
        ];
    }
}
