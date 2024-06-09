<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:12 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Order;

use App\Actions\Traits\WithActionUpdate;
use App\Enums\Ordering\Order\OrderStateEnum;
use App\Models\Ordering\Order;
use Illuminate\Validation\ValidationException;

class UpdateStateToHandlingOrder
{
    use WithActionUpdate;
    use HasOrderHydrators;

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function handle(Order $order): Order
    {
        $data = [
            'state' => \App\Enums\Ordering\Order\OrderStateEnum::HANDLING
        ];

        if (in_array($order->state, [OrderStateEnum::SUBMITTED, \App\Enums\Ordering\Order\OrderStateEnum::PACKED])) {
            $order->transactions()->update($data);

            $data[$order->state->value . '_at'] = null;
            $data['handling_at']                = now();

            $this->update($order, $data);

            $this->orderHydrators($order);

            return $order;
        }

        throw ValidationException::withMessages(['status' => 'You can not change the status to handling']);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function action(Order $order): Order
    {
        return $this->handle($order);
    }
}
