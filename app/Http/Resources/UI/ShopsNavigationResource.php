<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 03 Mar 2023 19:55:11 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\UI;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $slug
 * @property string $code
 * @property string $name
 *
 */
class ShopsNavigationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'slug' => $this->slug,
            'code' => $this->code,
            'name' => $this->name,
        ];
    }
}
