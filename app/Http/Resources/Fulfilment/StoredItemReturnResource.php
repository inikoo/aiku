<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Fulfilment;

use App\Enums\Fulfilment\StoredItemReturn\StoredItemReturnStateEnum;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class StoredItemReturnResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var \App\Models\Fulfilment\StoredItemReturn $storedItemReturn */
        $storedItemReturn = $this;

        $timeline = [];
        foreach (StoredItemReturnStateEnum::cases() as $state) {
            $timeline[$state->value] = [
                'label'   => $state->labels()[$state->value],
                'tooltip' => $state->labels()[$state->value],
                'key'     => $state->value,
                /*  'icon'      => $storedItemReturn->state->stateIcon()[$state->value]['icon'], */
                'timestamp' => $storedItemReturn->{$state->snake() . '_at'} ? $storedItemReturn->{$state->snake() . '_at'}->toISOString() : null
            ];
        }

        $finalTimeline = Arr::except(
            $timeline,
            [$storedItemReturn->state->value == StoredItemReturnStateEnum::CANCEL->value
                ? ''
                : StoredItemReturnStateEnum::CANCEL->value]
        );

        return [
            'id'               => $storedItemReturn->id,
            'reference'        => $storedItemReturn->reference,
            'state'            => $storedItemReturn->state,
            'timeline'         => $finalTimeline,
            'number_items'     => $storedItemReturn->items()->count()
        ];
    }
}
