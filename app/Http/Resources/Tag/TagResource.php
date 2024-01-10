<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 13 Oct 2022 15:56:55 Central European Summer Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Tag;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property mixed $type
 * @property \Illuminate\Support\Carbon $created_at
 */
class TagResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'slug'       => $this->slug,
            'name'       => $this->name,
            'type'       => $this->type,
            'created_at' => $this->created_at,
        ];
    }
}
