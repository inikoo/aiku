<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 22:08:01 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Ordering;

use Illuminate\Http\Resources\Json\JsonResource;

class NonProductItemsResource extends JsonResource
{
    public function toArray($request): array
    {
        $transaction = $this;
        return [
            'id'                             => $transaction->id,
            'state'                          => $transaction->state,
            'status'                         => $transaction->status,
            'quantity_ordered'               => intVal($transaction->quantity_ordered),
            'quantity_bonus'                 => intVal($transaction->quantity_bonus),
            'quantity_dispatched'            => intVal($transaction->quantity_dispatched),
            'quantity_fail'                  => intVal($transaction->quantity_fail),
            'quantity_cancelled'             => intVal($transaction->quantity_cancelled),
            'gross_amount'                   => $transaction->gross_amount,
            'net_amount'                     => $transaction->net_amount,
            'asset_name'                     => $transaction->asset_name,
            'model_type'                     => $transaction->model_type,
            'created_at'                     => $transaction->created_at,
            'currency_code'                  => $transaction->currency_code,

            'deleteRoute'            => [
                'name'       => 'grp.models.order.transaction.delete',
                'parameters' => [
                    'order'       => $transaction->order_id,
                    'transaction' => $transaction->id
                    ]
            ]
        ];
    }
}
