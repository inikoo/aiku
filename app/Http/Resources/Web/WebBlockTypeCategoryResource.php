<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 21 Jun 2024 01:16:49 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Web;

use App\Http\Resources\HasSelfCall;
use Illuminate\Http\Resources\Json\JsonResource;

class WebBlockTypeCategoryResource extends JsonResource
{
    use HasSelfCall;

    public function toArray($request): array
    {
        /** @var \App\Models\Web\WebBlockTypeCategory $webBlockTypeCategory */
        $webBlockTypeCategory = $this;

        return [
            'id' => $webBlockTypeCategory->id,
            'icon' => $webBlockTypeCategory->icon,
            'name' => $webBlockTypeCategory->name,
            'scope' => $webBlockTypeCategory->scope,
            'webBlockTypes' => WebBlockTypesResource::collection($webBlockTypeCategory->webBlockTypes)
        ];
    }
}
