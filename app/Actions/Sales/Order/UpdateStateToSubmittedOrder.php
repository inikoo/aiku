<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 10 May 2023 10:24:04 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\Order;

use App\Actions\Sales\Order\Traits\HasHydrators;
use App\Actions\WithActionUpdate;
use App\Enums\Sales\Order\OrderStateEnum;
use App\Models\Sales\Order;
use Illuminate\Validation\ValidationException;

class UpdateStateToSubmittedOrder
{
    use WithActionUpdate;
    use HasHydrators;

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function handle(Order $order): Order
    {
        $data = [
            'state' => OrderStateEnum::SUBMITTED
        ];

        if (in_array($order->state, [OrderStateEnum::CREATING, OrderStateEnum::HANDLING])) {
            $order->transactions()->update($data);

            if($order->state !== OrderStateEnum::CREATING) {
                $data[$order->state->value . '_at'] = null;
            }
            $data['submitted_at'] = now();

            $this->update($order, $data);

            $this->orderHydrators($order);

            return $order;
        }

        throw ValidationException::withMessages(['status' => 'You can not change the status to submitted']);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function action(Order $order): Order
    {
        return $this->handle($order);
    }
}
