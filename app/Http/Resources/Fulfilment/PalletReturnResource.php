<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Fulfilment;

use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use Illuminate\Http\Resources\Json\JsonResource;

class PalletReturnResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var \App\Models\Fulfilment\PalletReturn $palletReturn */
        $palletReturn = $this;

        $timeline = [];
        foreach (PalletReturnStateEnum::cases() as $state) {
            $timeline[$state->value] = [
                'label'   => $state->labels()[$state->value],
                'tooltip' => $state->labels()[$state->value],
                'key'     => $state->value,
                /*  'icon'      => $palletReturn->state->stateIcon()[$state->value]['icon'], */
                'timestamp' => $palletReturn->{$state->snake() . '_at'} ? $palletReturn->{$state->snake() . '_at'}->toISOString() : null
            ];
        }

        return [
            'id'               => $palletReturn->id,
            'reference'        => $palletReturn->reference,
            'state'            => $palletReturn->state,
            'timeline'         => $timeline,
            'number_pallets'   => $palletReturn->number_pallets
        ];
    }
}
