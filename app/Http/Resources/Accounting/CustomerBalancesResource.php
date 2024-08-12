<?php
/*
 *  Author: Jonathan lopez <raul@inikoo.com>
 *  Created: Sat, 22 Oct 2022 18:53:15 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, inikoo
 */

namespace App\Http\Resources\Accounting;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $id
 * @property string $slug
 * @property string $name
 * @property string $code
 * @property mixed  $balance
 */
class CustomerBalancesResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                            => $this->id,
            'slug'                          => $this->slug,
            'name'                          => $this->name,
            'balance'                       => $this->balance
        ];
    }
}
