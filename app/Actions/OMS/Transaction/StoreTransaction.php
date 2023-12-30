<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:32 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\OMS\Transaction;

use App\Models\OMS\Order;
use App\Models\OMS\Transaction;
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

    public function action(Order $order, array $modelData): Transaction
    {
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($order, $validatedData);
    }
}
