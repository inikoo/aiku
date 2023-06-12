<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 21 Oct 2021 12:37:51 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Http\Resources\HumanResources;

use App\Models\ClockingMachine;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class ClockingMachineResource extends JsonResource
{
    public function toArray($request): array|Arrayable|JsonSerializable
    {
        /** @var ClockingMachine $clockingMachine */
        $clockingMachine = $this;


        return [
            'id'            => $clockingMachine->id,
            'slug'          => $clockingMachine->slug,
            'code'          => $clockingMachine->code,
            'workplace_id'  => $clockingMachine->workplace_id,
            'data'          => $clockingMachine->data,
            'created_at'    => $clockingMachine->created_at,
            'updated_at'    => $clockingMachine->updated_at,
        ];
    }
}
