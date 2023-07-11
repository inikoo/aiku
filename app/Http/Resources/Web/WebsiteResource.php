<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 11 Jul 2023 12:17:22 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Web;

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
