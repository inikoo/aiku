<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 07 Jun 2023 14:32:13 Malaysia Time, Kuala Lumpur, Malaysia
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
class WebsitesNavigationResource extends JsonResource
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
