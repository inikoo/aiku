<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 May 2024 14:50:38 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Manufacturing;

use App\Models\Manufacturing\Artifact;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtifactResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Artifact $artifact */
        $artifact=$this;
        return [
            'id'      => $artifact->id,
            'slug'    => $artifact->slug,
        ];
    }
}
