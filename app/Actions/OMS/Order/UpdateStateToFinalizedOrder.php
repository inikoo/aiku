<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:12 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\OMS\Order;

use App\Actions\OMS\Order\Traits\HasHydrators;
use App\Actions\WithActionUpdate;
use App\Models\OMS\Order;
use Illuminate\Validation\ValidationException;

class UpdateStateToFinalizedOrder
{
    use WithActionUpdate;
    use HasHydrators;

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function handle(Order $order): Order
    {
        $data = [
            'state' => \App\Enums\OMS\Order\OrderStateEnum::FINALISED
        ];

        if (in_array($order->state, [\App\Enums\OMS\Order\OrderStateEnum::SETTLED, \App\Enums\OMS\Order\OrderStateEnum::PACKED])) {
            $order->transactions()->update($data);

            $data[$order->state->value . '_at'] = null;
            $data['packed_at']                  = now();

            $this->update($order, $data);

            $this->orderHydrators($order);

            return $order;
        }

        throw ValidationException::withMessages(['status' => 'You can not change the status to finalized']);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function action(Order $order): Order
    {
        return $this->handle($order);
    }
}
