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
            'id'         => $transaction->id,
            'state'  => $transaction->state,
            'status'       => $transaction->status,
            'quantity_ordered'       => $transaction->quantity_ordered,
            'quantity_bonus'      => $transaction->quantity_bonus,
            'quantity_dispatched' => $transaction->quantity_dispatched,
            'quantity_fail' => $transaction->quantity_fail,
            'quantity_cancelled'  => $transaction->quantity_cancelled,
            'gross_amount'        => $transaction->gross_amount,
            'net_amount'          => $transaction->net_amount,
            'asset_code'          => $transaction->asset_code,
            'asset_name'          => $transaction->asset_name,
            'asset_type'          => $transaction->asset_type,
            'created_at'          => $transaction->created_at
        ];
    }
}
