<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 12 Jun 2024 12:53:40 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Api\Dropshipping;

use App\Models\Catalogue\Shop;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $code
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string $warehouse_area_slug
 * @property mixed $type
 */
class ShopsResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Shop $shop */
        $shop = Shop::find($this->id);

        return [
            'id'      => $shop->id,
            'slug'    => $shop->slug,
            'code'    => $shop->code,
            'name'    => $shop->name,
            'state'   => $shop->state,
            'created' => $shop->created_at,
            'updated' => $shop->updated_at,
            'website' => WebsitesResource::make($shop->website),
        ];
    }
}
