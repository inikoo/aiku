<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 13:32:19 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\Transaction;

use App\Models\Sales\Transaction;
use App\Models\Sales\Order;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreTransaction
{
    use AsAction;
    use WithAttributes;

    public function handle(Order $order, array $modelData): Transaction
    {
        $modelData['shop_id']         = $order->shop_id;
        $modelData['customer_id']     = $order->customer_id;

        /** @var Transaction $transaction */
        $transaction = $order->transactions()->create($modelData);
        return $transaction;
    }

    public function rules(): array
    {
        return [
            'type'             => ['required'],
            'quantity_bonus'   => ['required', 'numeric'],
            'quantity_ordered' => ['required', 'numeric'],
        ];
    }

    public function action(Order $order, array $objectData): Transaction
    {
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($order, $validatedData);
    }
}
