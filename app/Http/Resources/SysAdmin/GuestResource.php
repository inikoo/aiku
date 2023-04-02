<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 15 Dec 2021 03:23:38 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Http\Resources\SysAdmin;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class GuestResource extends JsonResource
{
    /** @noinspection PhpUndefinedFieldInspection */
    public function toArray($request): array|Arrayable|JsonSerializable
    {
        $guest = $this;

        return [
            'id'         => $guest->id,
            'slug'       => $guest->slug,
            'name'       => $guest->name,
            'user'       => $guest->user?->only('username', 'status'),
            'created_at' => $guest->created_at,
            'updated_at' => $guest->updated_at,
        ];
    }
}
