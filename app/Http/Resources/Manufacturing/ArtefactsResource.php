<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 May 2024 16:10:36 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Manufacturing;

use Illuminate\Http\Resources\Json\JsonResource;

class ArtefactsResource extends JsonResource
{
    public function toArray($request): array
    {

        return [
            'slug'    => $this->slug,
            'code'    => $this->code,
        ];
    }
}
