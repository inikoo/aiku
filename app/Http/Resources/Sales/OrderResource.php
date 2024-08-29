<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 22 Feb 2023 22:40:53 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Sales;

use App\Enums\Ordering\Order\OrderStateEnum;
use App\Models\Ordering\Order;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Order $order */
        $order = $this;
        $timeline       = [];
        foreach (OrderStateEnum::cases() as $state) {

            $timeline[$state->value] = [
                'label'   => $state->labels()[$state->value],
                'tooltip' => $state->labels()[$state->value],
                'key'     => $state->value,
               /*  'icon'      => $palletDelivery->state->stateIcon()[$state->value]['icon'], */
                'timestamp' => $order->{$state->snake() . '_at'} ? $order->{$state->snake() . '_at'}->toISOString() : null
            ];
        }

        $finalTimeline = Arr::except(
            $timeline,
            [$order->state->value == OrderStateEnum::CANCELLED->value
                ? OrderStateEnum::DISPATCHED->value
                : OrderStateEnum::CANCELLED->value]
        );

        return [
            'id'            => $order->id,
            'reference'     => $order->reference,
            'state'         => $order->state->value,
            'timeline'      => $finalTimeline,
            'state_label'   => $order->state->labels()[$order->state->value],
            'state_icon'    => $order->state->stateIcon()[$order->state->value],
        ];
    }
}
