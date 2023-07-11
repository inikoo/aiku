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
 * @property string $code
 * @property string $type
 * @property mixed $created_at
 * @property mixed $updated_at
 */
class WebpageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'slug'            => $this->slug,
            'code'            => $this->code,
            'type'            => $this->type,
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
        ];
    }
}
