<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 21 Oct 2021 12:37:51 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Http\Resources\HumanResources;

use App\Models\HumanResources\ClockingMachine;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use JsonSerializable;

class ClockingMachinesResource extends JsonResource
{
    public function toArray($request): array|Arrayable|JsonSerializable
    {
        /** @var ClockingMachine $clockingMachine */
        $clockingMachine = $this;

        return [
            'id'                     => $clockingMachine->id,
            'organisation_slug'      => $clockingMachine->organisation_slug,
            'workplace_slug'         => $clockingMachine->workplace_slug,
            'slug'                   => $clockingMachine->slug,
            'name'                   => $clockingMachine->name,
            'type'                   => $clockingMachine->type,
            'created_at'             => $clockingMachine->created_at,
            'updated_at'             => $clockingMachine->updated_at,
            'nfc_tag'                => Arr::get($clockingMachine->data, 'nfc_tag'),
            // 'workplace'         => new WorkplaceResource($clockingMachine->workplace),
        ];
    }
}
