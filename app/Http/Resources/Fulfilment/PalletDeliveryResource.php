<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Fulfilment;

use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Models\Fulfilment\PalletDelivery;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class PalletDeliveryResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var PalletDelivery $palletDelivery */
        $palletDelivery = $this;
        $timeline       = [];
        foreach (PalletDeliveryStateEnum::cases() as $state) {

            $timeline[$state->value] = [
                'label'   => $state->labels()[$state->value],
                'tooltip' => $state->labels()[$state->value],
                'key'     => $state->value,
               /*  'icon'      => $palletDelivery->state->stateIcon()[$state->value]['icon'], */
                'timestamp' => $palletDelivery->{$state->snake() . '_at'} ? $palletDelivery->{$state->snake() . '_at'}->toISOString() : null
            ];
        }

        $finalTimeline = Arr::except(
            $timeline,
            [$palletDelivery->state->value == PalletDeliveryStateEnum::NOT_RECEIVED->value
                ? PalletDeliveryStateEnum::BOOKED_IN->value
                : PalletDeliveryStateEnum::NOT_RECEIVED->value]
        );

        // dd(PalletDeliveryStateEnum::stateIcon()[$palletDelivery->state->value]);

        return [
            'id'               => $palletDelivery->id,
            'customer_name'    => $palletDelivery->fulfilmentCustomer->customer->name,
            'reference'        => $palletDelivery->reference,
            'state'            => $palletDelivery->state->value,
            'timeline'         => $finalTimeline,
            'number_pallets'   => $palletDelivery->number_pallets,
            'state_label'      => $palletDelivery->state->labels()[$palletDelivery->state->value],
            'state_icon'       => $palletDelivery->state->stateIcon()[$palletDelivery->state->value],
            'estimated_delivery_date' => $palletDelivery->estimated_delivery_date
        ];
    }
}
