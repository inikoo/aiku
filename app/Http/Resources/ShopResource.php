<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 12 Oct 2022 16:35:42 Central European Summer Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopResource extends JsonResource
{

    public function toArray($request): array|\JsonSerializable|Arrayable
    {
        return parent::toArray($request);
    }
}
