<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 21 Oct 2021 12:37:51 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Http\Resources\HumanResources;

use App\Models\HumanResources\Clocking;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class ClockingResource extends JsonResource
{
    public function toArray($request): array|Arrayable|JsonSerializable
    {
        /** @var Clocking $clocking */
        $clocking = $this;


        return [
            'id'                    => $clocking->id,
            'slug'                  => $clocking->slug,
            'type'                  => $clocking->type,
            'workplace_id'          => $clocking->workplace_id,
            'clocking_machine_id'   => $clocking->clocking_machine_id,
            'notes'                 => $clocking->notes,

            'created_at'            => $clocking->created_at,
            'updated_at'            => $clocking->updated_at,
        ];
    }
}
