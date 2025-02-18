<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Fulfilment;

use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class PalletReturnResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var \App\Models\Fulfilment\PalletReturn $palletReturn */
        $palletReturn = $this;

        $timeline = [];
        foreach (PalletReturnStateEnum::cases() as $state) {
            $timeline[$state->value] = [
                'label'     => $state->labels()[$state->value],
                'tooltip'   => $state->labels()[$state->value],
                'key'       => $state->value,
                'timestamp' => $palletReturn->{$state->snake().'_at'} ? $palletReturn->{$state->snake().'_at'}->toISOString() : null
            ];
        }

        $finalTimeline = Arr::except(
            $timeline,
            [
                $palletReturn->state->value == PalletReturnStateEnum::CANCEL->value
                    ? ''
                    : PalletReturnStateEnum::CANCEL->value
            ]
        );

        return [
            'id'                    => $palletReturn->id,
            'customer_reference'    => $palletReturn->customer_reference,
            'reference'             => $palletReturn->reference,
            'state'                 => $palletReturn->state,
            'is_collection'         => $palletReturn->is_collection,
            'state_label'           => $palletReturn->state->labels()[$palletReturn->state->value],
            'state_icon'            => $palletReturn->state->stateIcon()[$palletReturn->state->value],
            'type'                  => $palletReturn->type,
            'type_label'            => $palletReturn->type->labels()[$palletReturn->type->value],
            'type_icon'             => $palletReturn->type->stateIcon()[$palletReturn->type->value],
            'timeline'              => $finalTimeline,
            'number_pallets'        => $palletReturn->stats->number_pallets,
            'number_stored_items'   => $palletReturn->stats->number_stored_items,
            'number_services'       => $palletReturn->stats->number_services,
            'number_physical_goods' => $palletReturn->stats->number_physical_goods,
        ];
    }
}
