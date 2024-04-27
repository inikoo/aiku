<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 08:27:45 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Market;

use App\Models\Market\CollectionCategory;
use Illuminate\Http\Resources\Json\JsonResource;

class CollectionCategoryResource extends JsonResource
{
    public function toArray($request): array
    {

        /** @var CollectionCategory $collectionCategory */
        $collectionCategory=$this;

        return [
            'slug'              => $collectionCategory->slug,
            'code'              => $collectionCategory->code,
            'name'              => $collectionCategory->name,
            'description'       => $collectionCategory->description,
            'created_at'        => $collectionCategory->created_at,
            'updated_at'        => $collectionCategory->updated_at,
        ];
    }
}
