<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Fulfilment;

use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use Illuminate\Http\Resources\Json\JsonResource;

class PalletDeliveryResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var \App\Models\Fulfilment\PalletDelivery $palletDelivery */
        $palletDelivery = $this;

        $timeline = [];
        foreach (PalletDeliveryStateEnum::cases() as $state) {
            $timeline[] = [
                'label'   => $state->labels()[$state->value],
                'tooltip' => $state->labels()[$state->value],
                'key'     => $state->value,
               /*  'icon'      => $palletDelivery->state->stateIcon()[$state->value]['icon'], */
                'timestamp' => $palletDelivery->{$state->snake() . '_at'} ? $palletDelivery->{$state->snake() . '_at'}->toISOString() : null
            ];
        }

        return [
            'id'               => $palletDelivery->id,
            'reference'        => $palletDelivery->reference,
            'state'            => $palletDelivery->state,
            'timeline'         => $timeline,
            'number_pallets'   => $palletDelivery->number_pallets,
        ];
    }
}
