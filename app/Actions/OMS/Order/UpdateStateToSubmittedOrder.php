<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:11 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\OMS\Order;

use App\Actions\OMS\Order\Traits\HasHydrators;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\OMS\Order\OrderStateEnum;
use App\Models\OMS\Order;
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
            'state' => \App\Enums\OMS\Order\OrderStateEnum::SUBMITTED
        ];

        if (in_array($order->state, [\App\Enums\OMS\Order\OrderStateEnum::CREATING, \App\Enums\OMS\Order\OrderStateEnum::HANDLING])) {
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
