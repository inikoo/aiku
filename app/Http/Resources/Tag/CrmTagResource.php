<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 15 Nov 2023 17:29:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Tag;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $tag_slug
 * @property string $label
 * @property integer $number_prospects
 */
class CrmTagResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'tag_slug'         => $this->tag_slug,
            'label'            => $this->label,
            'number_prospects' => $this->number_prospects
        ];
    }
}
