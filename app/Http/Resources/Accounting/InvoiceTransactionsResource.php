<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:38:56 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Accounting;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $code
 * @property string $quantity
 * @property string $net_amount
 * @property string $description
 */

class InvoiceTransactionsResource extends JsonResource
{
    public function toArray($request): array
    {
        // dd($this);
        return [
            'code'        => $this->code,
            'name'        => $this->name,
            'quantity'    => $this->quantity,
            'price'       => $this->net_amount,
            'description' => $this->description,
        ];
    }
}
