<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 16 May 2024 21:27:02 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\HumanResources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property mixed $starts_at
 * @property mixed $ends_at
 * @property mixed $duration
 */
class TimeTrackersResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'        => $this->id,
            'starts_at' => $this->starts_at,
            'ends_at'   => $this->ends_at,
            'duration'  => $this->duration,

        ];
    }
}
