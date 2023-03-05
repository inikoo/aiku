<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 27 Nov 2022 22:18:55 Central Indonesia Time, Ubud, Bali, Indonesia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Fulfilment;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $number
 */
class FulfilmentOrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'     => $this->id,
            'number' => $this->number,
        ];
    }
}
