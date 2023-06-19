<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 13 Oct 2022 15:57:30 Central European Summer Time, Plane Malaga - East Midlands UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Marketing;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $slug
 * @property string $name
 * @property string $code
 * @property string $domain
 * @property string $state
 * @property boolean $in_maintenance
 */
class WebsiteResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'slug'           => $this->slug,
            'shop_slug'      => $this->whenHas('shop_slug'),
            'code'           => $this->code,
            'name'           => $this->name,
            'domain'         => $this->domain,
            'state'          => $this->state,
            'in_maintenance' => $this->in_maintenance


        ];
    }
}
