<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Apr 2024 09:40:37 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Ordering;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $slug
 * @property string $code
 * @property mixed $created_at
 * @property mixed $updated_at
 * @property string $name
 * @property string $state
 * @property string $shop_slug
 * @property string $date
 * @property string $reference
 *
 */
class TransactionsResource extends JsonResource
{
    public function toArray($request): array
    {
        $transaction = $this;
        return [
            'id'                     => $transaction->id,
            'state'                  => $transaction->state,
            'status'                 => $transaction->status,
            'quantity_ordered'       => intVal($transaction->quantity_ordered),
            'quantity_bonus'         => intVal($transaction->quantity_bonus),
            'quantity_dispatched'    => intVal($transaction->quantity_dispatched),
            'quantity_fail'          => intVal($transaction->quantity_fail),
            'quantity_cancelled'     => intVal($transaction->quantity_cancelled),
            'gross_amount'           => $transaction->gross_amount,
            'net_amount'             => $transaction->net_amount,
            'asset_code'             => $transaction->asset_code,
            'asset_name'             => $transaction->asset_name,
            'asset_type'             => $transaction->asset_type,
            'product_slug'           => $transaction->product_slug,
            'created_at'             => $transaction->created_at,
            'currency_code'          => $transaction->currency_code,

            'deleteRoute'            => [
                'name'       => 'grp.models.order.transaction.delete',
                'parameters' => [
                    'order'       => $transaction->order_id,
                    'transaction' => $transaction->id
                ],
                'method'    => 'delete'
            ],
            'updateRoute'            => [
                'name'       => 'grp.models.order.transaction.update',
                'parameters' => [
                    'order'       => $transaction->order_id,
                    'transaction' => $transaction->id
                ],
                'method'    => 'patch'
            ],
        ];
    }
}
