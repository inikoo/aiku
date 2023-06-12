<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 21 Oct 2021 12:37:51 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Http\Resources\HumanResources;

use App\Models\HumanResources\Workplace;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class WorkPlaceResource extends JsonResource
{
    public function toArray($request): array|Arrayable|JsonSerializable
    {
        /** @var Workplace $workplace */
        $workplace = $this;


        return [
            'id'         => $workplace->id,
            'slug'       => $workplace->slug,
            'name'       => $workplace->name,
            'type'       => $workplace->type,

            'created_at' => $workplace->created_at,
            'updated_at' => $workplace->updated_at,
        ];
    }
}
